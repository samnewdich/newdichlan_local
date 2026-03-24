// ────────────────────────────────────────────────
// WiFi Manager for Paid Hotspot (MAC-based)
// Anti-Tethering / Hotspot-Sharing Protection
// Maintained by NEWDICH TECHNOLOGY
// ────────────────────────────────────────────────

const { exec } = require('child_process');
const axios = require('axios');

const DOWNSTREAM_IFACE = "eth0";
const UPSTREAM_IFACE   = "wlan0";
const GATEWAY_IP       = "192.168.200.1";
const CHECK_PAID_API   = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid";
const PORTAL_PORT      = 80;

// ── Anti-Tethering Config ──
const MAX_CONNECTIONS_PER_MAC = 60;    // Max concurrent connections per device
const TETHER_TTL              = 63;    // TTL of packets from tethered devices
                                       // (phone TTL=64, tethered device arrives as 63)

const macState = {};

// ───────── EXEC HELPER ─────────
function run(cmd) {
  return new Promise((resolve) => {
    exec(cmd, (err, stdout, stderr) => {
      resolve({ err, stdout, stderr });
    });
  });
}

// ───────── INITIAL SETUP (run once at start) ─────────
// Sets up global iptables rules that apply to ALL devices
async function setupGlobalRules() {
  console.log("[SETUP] Applying global anti-tethering rules...");

  // ── 1. TTL-based tethering detection ──
  // Mobile phones send packets with TTL=64 (Android/iOS default).
  // When a phone tethers another device, that device's packets arrive
  // at your router with TTL=63 (decremented by one hop through the phone).
  // We drop any forwarded packet with TTL exactly 63 coming from the hotspot side.
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq ${TETHER_TTL} -j DROP 2>/dev/null || true`);
  await run(`iptables -I FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq ${TETHER_TTL} -j DROP`);

  // Also catch TTL=127 (Windows tethered devices start at 128, arrive as 127)
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq 127 -j DROP 2>/dev/null || true`);
  await run(`iptables -I FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq 127 -j DROP`);

  // ── 2. Block DHCP re-broadcasting ──
  // A tethering phone runs its own DHCP server and broadcasts DHCP offers.
  // Drop any DHCP server traffic (port 67) originating from client-side.
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 67 -j DROP 2>/dev/null || true`);
  await run(`iptables -I FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 67 -j DROP`);

  // ── 3. Block DNS re-broadcasting on downstream ──
  // Tethering phones often run a local DNS proxy. Drop DNS traffic
  // that is being forwarded from a client (not going to our gateway).
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 53 -j DROP 2>/dev/null || true`);
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p tcp --dport 53 -j DROP 2>/dev/null || true`);
  await run(`iptables -I FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 53 -j DROP`);
  await run(`iptables -I FORWARD -i ${DOWNSTREAM_IFACE} -p tcp --dport 53 -j DROP`);

  // ── 4. Ensure kernel modules are loaded ──
  await run(`modprobe xt_ttl 2>/dev/null || true`);
  await run(`modprobe xt_connlimit 2>/dev/null || true`);

  console.log("[SETUP] Global rules applied.");
}

// ───────── PER-MAC CONNECTION LIMIT ─────────
// Limits how many simultaneous connections a single MAC can have.
// A phone browsing normally needs ~20-40. Tethering shoots this up fast.
async function applyConnectionLimit(mac) {
  await run(`iptables -D FORWARD -m mac --mac-source ${mac} -m connlimit --connlimit-above ${MAX_CONNECTIONS_PER_MAC} -j REJECT --reject-with tcp-reset 2>/dev/null || true`);
  await run(
    `iptables -I FORWARD -m mac --mac-source ${mac} \
     -m connlimit --connlimit-above ${MAX_CONNECTIONS_PER_MAC} \
     -j REJECT --reject-with tcp-reset`
  );
}

async function removeConnectionLimit(mac) {
  await run(`iptables -D FORWARD -m mac --mac-source ${mac} -m connlimit --connlimit-above ${MAX_CONNECTIONS_PER_MAC} -j REJECT --reject-with tcp-reset 2>/dev/null || true`);
}

// ───────── BLOCK DEVICE ─────────
async function blockDevice(mac) {
  if (macState[mac] === "blocked") return;

  console.log(`[BLOCK] ${mac}`);

  await run(`iptables -D FORWARD -m mac --mac-source ${mac} -j ACCEPT 2>/dev/null || true`);
  await removeConnectionLimit(mac);

  const { err: checkErr } = await run(`iptables -C FORWARD -m mac --mac-source ${mac} -j DROP 2>/dev/null`);
  if (checkErr) {
    const { err } = await run(`iptables -I FORWARD -m mac --mac-source ${mac} -j DROP`);
    if (err) console.error(`  DROP failed for ${mac}:`, err.message);
  }

  const { err: nat80 } = await run(`iptables -t nat -C PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null`);
  if (nat80) await run(`iptables -t nat -A PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT}`);

  const { err: nat443 } = await run(`iptables -t nat -C PREROUTING -m mac --mac-source ${mac} -p tcp --dport 443 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null`);
  if (nat443) await run(`iptables -t nat -A PREROUTING -m mac --mac-source ${mac} -p tcp --dport 443 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT}`);

  macState[mac] = "blocked";
  console.log(`  ✓ Blocked ${mac}`);
}

// ───────── UNBLOCK DEVICE ─────────
async function unblockDevice(mac) {
  if (macState[mac] === "allowed") return;

  console.log(`[UNBLOCK] ${mac}`);

  await run(`iptables -D FORWARD -m mac --mac-source ${mac} -j DROP 2>/dev/null || true`);
  await run(`iptables -t nat -D PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null || true`);
  await run(`iptables -t nat -D PREROUTING -m mac --mac-source ${mac} -p tcp --dport 443 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null || true`);

  const { err: checkErr } = await run(`iptables -C FORWARD -m mac --mac-source ${mac} -j ACCEPT 2>/dev/null`);
  if (checkErr) {
    const { err } = await run(`iptables -I FORWARD -m mac --mac-source ${mac} -j ACCEPT`);
    if (err) console.error(`  ACCEPT failed for ${mac}:`, err.message);
  }

  // Apply per-MAC connection limit to prevent tethering
  await applyConnectionLimit(mac);

  macState[mac] = "allowed";
  console.log(`  ✓ Unblocked ${mac} (conn limit: ${MAX_CONNECTIONS_PER_MAC})`);
}

// ───────── GET CONNECTED MACs ─────────
async function getConnectedMacs() {
  const { err, stdout } = await run(`arp -i ${DOWNSTREAM_IFACE} -a`);
  if (err) {
    console.error('ARP scan error:', err.message);
    return [];
  }

  const macs = [];
  stdout.split("\n").forEach(line => {
    const match = line.match(/at\s([0-9A-Fa-f]{2}(?::[0-9A-Fa-f]{2}){5})/i);
    if (match) {
      const mac = match[1].toUpperCase();
      if (mac !== "FF:FF:FF:FF:FF:FF" && mac !== "00:00:00:00:00:00") {
        macs.push(mac);
      }
    }
  });

  return [...new Set(macs)];
}

// ───────── SUBSCRIPTION CHECK LOOP ─────────
async function checkSubscriptions() {
  const macs = await getConnectedMacs();
  if (macs.length === 0) return;

  console.log(`[CHECK] ${macs.length} device(s): ${macs.join(', ')}`);

  for (const mac of macs) {
    try {
      const res = await axios.get(CHECK_PAID_API, {
        params: { mac, current_time: Math.floor(Date.now() / 1000) },
        timeout: 5000,
      });

      const { sub_status } = res.data;

      if (sub_status === "active") {
        await unblockDevice(mac);
      } else {
        await blockDevice(mac);
      }
    } catch (err) {
      console.error(`[API ERROR] ${mac}: ${err.message} → blocking as fail-safe`);
      await blockDevice(mac);
    }
  }
}

// ───────── CLEANUP ON EXIT ─────────
async function cleanup() {
  console.log("\n[CLEANUP] Flushing all rules...");

  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq ${TETHER_TTL} -j DROP 2>/dev/null || true`);
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -m ttl --ttl-eq 127 -j DROP 2>/dev/null || true`);
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 67 -j DROP 2>/dev/null || true`);
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p udp --dport 53 -j DROP 2>/dev/null || true`);
  await run(`iptables -D FORWARD -i ${DOWNSTREAM_IFACE} -p tcp --dport 53 -j DROP 2>/dev/null || true`);

  for (const mac in macState) {
    await run(`iptables -D FORWARD -m mac --mac-source ${mac} -j ACCEPT 2>/dev/null || true`);
    await run(`iptables -D FORWARD -m mac --mac-source ${mac} -j DROP 2>/dev/null || true`);
    await run(`iptables -D FORWARD -m mac --mac-source ${mac} -m connlimit --connlimit-above ${MAX_CONNECTIONS_PER_MAC} -j REJECT --reject-with tcp-reset 2>/dev/null || true`);
    await run(`iptables -t nat -D PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null || true`);
    await run(`iptables -t nat -D PREROUTING -m mac --mac-source ${mac} -p tcp --dport 443 -j DNAT --to-destination ${GATEWAY_IP}:${PORTAL_PORT} 2>/dev/null || true`);
  }

  console.log("[CLEANUP] Done.");
  process.exit(0);
}

process.on('SIGINT', cleanup);
process.on('SIGTERM', cleanup);

// ───────── START ─────────
console.log("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
console.log("  WiFi Manager — MAC-based + Anti-Tethering");
console.log("  Maintained by NEWDICH TECHNOLOGY");
console.log(`  Downstream : ${DOWNSTREAM_IFACE}  |  Upstream: ${UPSTREAM_IFACE}`);
console.log(`  Gateway    : ${GATEWAY_IP}`);
console.log(`  API        : ${CHECK_PAID_API}`);
console.log(`  Max conns  : ${MAX_CONNECTIONS_PER_MAC} per MAC`);
console.log("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

(async () => {
  await setupGlobalRules();
  await checkSubscriptions();
  setInterval(checkSubscriptions, 10000);
})();
