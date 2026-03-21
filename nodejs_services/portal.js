// ────────────────────────────────────────────────
// NEWDICH CAPTIVE PORTAL (portal.js)
// ────────────────────────────────────────────────

const express = require('express');
const { exec } = require('child_process');

const app = express();

// ───────── CONFIG ─────────
const GATEWAY_IP = "192.168.200.1";
const DOWNSTREAM_IFACE = "eth0";

// Your Apache payment page (LAMP on port 8080)
const PAYMENT_URL = "http://192.168.200.1:8080/newdichlan/ansofra/pay";

// Cache for IP → MAC
let arpCache = {};

// ────────────────────────────────────────────────
// UPDATE ARP TABLE (IP → MAC)
// ────────────────────────────────────────────────
function updateArpCache() {
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
}

// Refresh every 5 seconds
setInterval(updateArpCache, 5000);

// ────────────────────────────────────────────────
// GET DEVICE NAME (HOSTNAME)
// ────────────────────────────────────────────────
function getHostname(ip) {
  return new Promise((resolve) => {
    exec(`nmblookup -A ${ip}`, (err, stdout) => {
      if (err || !stdout) return resolve("UNKNOWN");

      const match = stdout.match(/<00>\s+UNIQUE\s+(.+)/);
      if (match) {
        resolve(match[1].trim());
      } else {
        resolve("UNKNOWN");
      }
    });
  });
}

// ────────────────────────────────────────────────
// MAIN CAPTIVE PORTAL HANDLER
// ────────────────────────────────────────────────
app.use(async (req, res) => {

  // Get client IP
  let ip = req.connection.remoteAddress || "";
  ip = ip.replace("::ffff:", "");

  // Get MAC from ARP cache
  const mac = arpCache[ip] || "UNKNOWN";

  // Try to get device name
  const deviceName = await getHostname(ip);

  console.log(`Device connected → IP: ${ip}, MAC: ${mac}, NAME: ${deviceName}`);

  // Build redirect URL with params
  const redirectUrl =
    `${PAYMENT_URL}?mac=${encodeURIComponent(mac)}&ip=${encodeURIComponent(ip)}&device=${encodeURIComponent(deviceName)}&current_time=${Math.floor(Date.now() / 1000)}`;

  // Redirect user to payment page
  res.redirect(302, redirectUrl);
});

// ────────────────────────────────────────────────
// START SERVER
// ────────────────────────────────────────────────
app.listen(80, GATEWAY_IP, () => {
  console.log(`Captive portal running on http://${GATEWAY_IP}`);
});




//NOTES TO THIS SCRIPT
//REQUIRED PACKAGES
//Install:
//npm init -y
//npm install express
//IMPORTANT SYSTEM REQUIREMENT
//For device name detection:
//sudo apt install samba
//This provides:
//nmblookup
//VERY IMPORTANT (iptables)
//Make sure your redirect rule is correct:
//# Redirect ALL HTTP traffic to portal
//iptables -t nat -A PREROUTING -i eth0 -p tcp --dport 80 -j DNAT --to 192.168.200.1:80
//HOW IT WORKS
//When user connects:
//Phone connects to AX55
//User opens any site
//iptables redirects → portal.js
//Node.js:
//Gets IP
//Maps MAC via ARP
//Tries device name
//Redirects to:
//http://192.168.200.1:8080/payment?mac=XX&ip=XX&device=XX
//REALITY CHECK (VERY IMPORTANT)
//Device Name:
//Works for Windows
//Sometimes works for Android
//Rarely works for iPhone
//So:
//device = "UNKNOWN" sometimes → normal

//OPTIONAL IMPROVEMENT (RECOMMENDED)
//If you want faster + reliable system:
//Remove hostname lookup (it slows things)
//Use only:
//MAC
//IP