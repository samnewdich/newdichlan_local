// ────────────────────────────────────────────────
// WiFi Manager (Subscription-Based Access Control)
// Maintained by NEWDICH TECHNOLOGY
// ────────────────────────────────────────────────

const { exec } = require('child_process');
const axios = require('axios');

// ───────── CONFIG ─────────
const DOWNSTREAM_IFACE = "eth0";     // Hotspot interface
const UPSTREAM_IFACE   = "wlan0";    // Internet interface
const GATEWAY_IP       = "192.168.200.1";
const SUBNET           = "192.168.200.0/24";

const CHECK_API = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid"; // returns { sub_status: "active" | "inactive" }

// Cache
let arpCache = {};

// ────────────────────────────────────────────────
// BLOCK DEVICE (NO INTERNET)
// ────────────────────────────────────────────────
function blockDevice(ip) {

  // Drop all forwarding traffic
  exec(`iptables -C FORWARD -s ${ip} -j DROP || \
        iptables -A FORWARD -s ${ip} -j DROP`);

  console.log(`BLOCKED → ${ip}`);
}

// ────────────────────────────────────────────────
// UNBLOCK DEVICE (FULL INTERNET)
// ────────────────────────────────────────────────
function unblockDevice(ip) {

  // Remove DROP rule
  exec(`iptables -D FORWARD -s ${ip} -j DROP || true`);

  // Allow forwarding to internet
  exec(`iptables -C FORWARD -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT || \
        iptables -I FORWARD 1 -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT`);

  console.log(`UNBLOCKED → ${ip}`);
}

// ────────────────────────────────────────────────
// UPDATE ARP CACHE (IP → MAC)
// ────────────────────────────────────────────────
setInterval(() => {
  exec(`arp -i ${DOWNSTREAM_IFACE} -a`, (err, stdout) => {
    if (err) return;

    stdout.split("\n").forEach(line => {
      const match = line.match(/\(([\d.]+)\)\sat\s([0-9A-Fa-f:]+)/);
      if (match) {
        const ip = match[1];
        const mac = match[2].toUpperCase();
        arpCache[ip] = mac;
      }
    });
  });
}, 5000);

// ────────────────────────────────────────────────
// SUBSCRIPTION CHECK LOOP
// ────────────────────────────────────────────────
setInterval(async () => {

  for (const ip in arpCache) {

    const mac = arpCache[ip];

    // Skip gateway
    if (ip === GATEWAY_IP) continue;

    try {
      const res = await axios.get(
        `${CHECK_API}?mac=${encodeURIComponent(mac)}&time=${Date.now()}`
      );

      const { sub_status } = res.data;

      //ACTIVE → UNBLOCK
      if (sub_status === "active") {
        unblockDevice(ip);
      }

      //INACTIVE → BLOCK
      else {
        blockDevice(ip);
      }

    } catch (err) {
      console.error(`API ERROR → ${mac}:`, err.message);

      // Fail-safe: block if API fails
      blockDevice(ip);
    }
  }

}, 10000); // every 10 seconds

// ────────────────────────────────────────────────
// START MESSAGE
// ────────────────────────────────────────────────
console.log("WiFi Manager running...");