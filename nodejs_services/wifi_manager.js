const { exec } = require('child_process');
const axios = require('axios');

const IFACE = "eth0";
const API = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid";

let deviceState = {};
let arpCache = {};

function run(cmd) {
  exec(cmd, () => {});
}

// BLOCK
function block(mac) {
  if (deviceState[mac] === "blocked") return;

  run(`iptables -C FORWARD -m mac --mac-source ${mac} -j DROP || \
       iptables -A FORWARD -m mac --mac-source ${mac} -j DROP`);

  deviceState[mac] = "blocked";
  console.log("BLOCKED:", mac);
}

// UNBLOCK
function unblock(mac, ip) {
  if (deviceState[mac] === "active") return;

  // Remove block
  run(`iptables -D FORWARD -m mac --mac-source ${mac} -j DROP || true`);

  // Bind MAC + IP (anti-spoof)
  run(`iptables -C FORWARD -s ${ip} -m mac --mac-source ${mac} -j ACCEPT || \
       iptables -I FORWARD 1 -s ${ip} -m mac --mac-source ${mac} -j ACCEPT`);

  deviceState[mac] = "active";
  console.log("UNBLOCKED:", mac, ip);
}

// ARP SCAN
setInterval(() => {
  exec(`arp -i ${IFACE} -a`, (err, stdout) => {
    if (err) return;

    stdout.split("\n").forEach(line => {
      const match = line.match(/\(([\d.]+)\)\sat\s([0-9A-Fa-f:]+)/);
      if (match) {
        const ip = match[1];
        const mac = match[2].toUpperCase();
        arpCache[mac] = ip;
      }
    });
  });
}, 5000);

// CHECK LOOP
setInterval(async () => {
  for (const mac in arpCache) {
    const ip = arpCache[mac];

    try {
      const res = await axios.get(`${API}?mac=${mac}&ip=${ip}&current_time=${Math.floor(Date.now() / 1000)}`);

      const { sub_status, session_valid } = res.data;

      if (sub_status === "active" && session_valid === true) {
        unblock(mac, ip);
      } else {
        block(mac);
      }

    } catch {
      block(mac);
    }
  }
}, 10000);

console.log("Secure WiFi Manager running...");