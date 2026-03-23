// ────────────────────────────────────────────────
// WiFi Manager for Paid Hotspot (MAC-based)
// Maintained by NEWDICH TECHNOLOGY
// ────────────────────────────────────────────────

const { exec } = require('child_process');
const axios = require('axios');

const DOWNSTREAM_IFACE = "eth0";       // Hotspot interface
const UPSTREAM_IFACE   = "wlan0";      // Internet interface
const GATEWAY_IP       = "192.168.200.1"; 
const CHECK_PAID_API   = "https://lan.newdich.tech/api/checkpaid";
const PORT             = 80;           // Local HTTP port (portal)

// MAC → IP mapping (dynamic ARP cache)
let arpCache = {};

// ───────── UPDATE ARP CACHE ─────────
function updateArpCache() {
  exec(`arp -i ${DOWNSTREAM_IFACE} -a`, (err, stdout) => {
    if (err) return console.error('ARP scan error:', err.message);

    stdout.split("\n").forEach(line => {
      const match = line.match(/\(([\d.]+)\)\sat\s([0-9A-Fa-f:]+)/);
      if (match) {
        const ip = match[1];
        const mac = match[2].toUpperCase();
        arpCache[mac] = ip;
      }
    });
  });
}

// Refresh ARP cache every 5 seconds
setInterval(updateArpCache, 5000);

// ───────── BLOCK / UNBLOCK FUNCTIONS ─────────
function blockDevice(mac) {
  const ip = arpCache[mac];
  if (!ip) return;

  // Remove any existing ACCEPT rules
  exec(`iptables -D FORWARD -m mac --mac-source ${mac} -j ACCEPT || true`);

  // Block everything from this MAC
  exec(`iptables -I FORWARD -m mac --mac-source ${mac} -j DROP`, err => {
    if (err) console.error(`Failed to block ${mac}:`, err.message);
    else console.log(`Blocked ${mac} (${ip})`);
  });

  // Optional: redirect HTTP to portal
  exec(`iptables -t nat -C PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to ${GATEWAY_IP}:${PORT} || \
        iptables -t nat -A PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to ${GATEWAY_IP}:${PORT}`);
}

function unblockDevice(mac) {
  const ip = arpCache[mac];
  if (!ip) return;

  // Remove DROP rules
  exec(`iptables -D FORWARD -m mac --mac-source ${mac} -j DROP || true`);

  // Remove HTTP redirect
  exec(`iptables -t nat -D PREROUTING -m mac --mac-source ${mac} -p tcp --dport 80 -j DNAT --to ${GATEWAY_IP}:${PORT} || true`);

  // Allow this MAC
  exec(`iptables -I FORWARD -m mac --mac-source ${mac} -j ACCEPT`, err => {
    if (err) console.error(`Failed to unblock ${mac}:`, err.message);
    else console.log(`Unblocked ${mac} (${ip})`);
  });
}

// ───────── SUBSCRIPTION CHECK LOOP ─────────
async function checkSubscriptions() {
  for (const mac in arpCache) {
    try {
      const res = await axios.get(`${CHECK_PAID_API}?mac=${encodeURIComponent(mac)}&current_time=${Math.floor(Date.now()/1000)}`);
      const { sub_status } = res.data;

      if (sub_status === "active") {
        unblockDevice(mac);
      } else {
        blockDevice(mac);
      }
    } catch (err) {
      console.error(`API error for ${mac}:`, err.message);
      blockDevice(mac); // fail-safe
    }
  }
}

// Run subscription check every 10 seconds
setInterval(checkSubscriptions, 10000);

console.log("WiFi manager running... MAC-based hotspot control");