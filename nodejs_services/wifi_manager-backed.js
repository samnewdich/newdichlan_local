const { exec } = require('child_process');
const axios = require('axios');

// CONFIG
const DOWNSTREAM_IFACE = "eth0";
const UPSTREAM_IFACE   = "wlan0";
const GATEWAY_IP       = "192.168.200.1";
const SUBNET           = "192.168.200.0/24";

const CHECK_API = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid";

// Cache
let arpCache = {};
let deviceState = {}; // track state

// BLOCK
function blockDevice(ip) {
    if (deviceState[ip] === "blocked") return;

    exec(`iptables -C FORWARD -s ${ip} -j DROP || iptables -A FORWARD -s ${ip} -j DROP`);

    deviceState[ip] = "blocked";
    console.log(`BLOCKED → ${ip}`);
}

// UNBLOCK
function unblockDevice(ip) {
    if (deviceState[ip] === "unblocked") return;

    exec(`iptables -D FORWARD -s ${ip} -j DROP || true`);

    exec(`iptables -C FORWARD -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT || \
          iptables -I FORWARD 1 -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT`);

    deviceState[ip] = "unblocked";
    console.log(`UNBLOCKED → ${ip}`);
}

// ARP SCAN
setInterval(() => {
  exec(`arp -i ${DOWNSTREAM_IFACE} -a`, (err, stdout) => {
    if (err) return;

    stdout.split("\n").forEach(line => {
      const match = line.match(/\(([\d.]+)\)\sat\s([0-9A-Fa-f:]+)/);
      if (match) {
        arpCache[match[1]] = match[2].toUpperCase();
      }
    });
  });
}, 10000);

// CHECK LOOP
setInterval(async () => {

  for (const ip in arpCache) {

    if (ip === GATEWAY_IP) continue;

    const mac = arpCache[ip];

    try {
      const res = await axios.get(
        `${CHECK_API}?mac=${encodeURIComponent(mac)}&current_time=${Math.floor(Date.now() / 1000)}`
      );

      const { sub_status } = res.data;

      if (sub_status === "active") {
        unblockDevice(ip);
      } else {
        blockDevice(ip);
      }

    } catch (err) {
      console.error(`API ERROR → ${mac}:`, err.message);
      blockDevice(ip);
    }
  }

}, 10000);

// START
console.log("WiFi Manager running...");



/*
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


//Block and unblock
async function blockDevice(ip) {
    exec(`iptables -C FORWARD -s ${ip} -j DROP || iptables -A FORWARD -s ${ip} -j DROP`, (err) => {
        if (err) console.error(`Error blocking ${ip}:`, err.message);
        else console.log(`BLOCKED → ${ip}`);
    });
}

async function unblockDevice(ip) {
    exec(`iptables -D FORWARD -s ${ip} -j DROP || true`, (err) => {
        if (err) console.error(`Error removing DROP for ${ip}:`, err.message);
    });

    exec(`iptables -C FORWARD -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT || \
          iptables -I FORWARD 1 -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT`, (err) => {
        if (err) console.error(`Error allowing ${ip}:`, err.message);
        else console.log(`UNBLOCKED → ${ip}`);
    });
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
}, 10000);

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
        `${CHECK_API}?mac=${encodeURIComponent(mac)}&time=${Math.floor(Date.now()/1000)}`
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
*/