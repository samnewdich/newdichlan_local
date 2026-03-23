// ────────────────────────────────────────────────
// WiFi Manager (Subscription + Anti-Hotspot Sharing)
// Maintained by NEWDICH TECHNOLOGY
// ────────────────────────────────────────────────

const { exec } = require('child_process');
const axios = require('axios');

// ───────── CONFIG ─────────
const DOWNSTREAM_IFACE = "eth0";       // Hotspot interface (AX55)
const UPSTREAM_IFACE   = "wlan0";      // Internet interface (modem/Starlink)
const GATEWAY_IP       = "192.168.200.1";
const SUBNET           = "192.168.200.0/24";

// Local API (on LAMP server running on Kali)
const CHECK_API = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid";

// Session store to prevent hotspot-sharing
const sessionStore = new Map(); // Key: MAC, Value: { ip, connectedAt }

// ───────── BLOCK / UNBLOCK FUNCTIONS ─────────
function blockDevice(ip, mac) {
    exec(`iptables -C FORWARD -s ${ip} -j DROP || iptables -A FORWARD -s ${ip} -j DROP`, (err) => {
        if (err) console.error(`Error blocking ${mac} (${ip}):`, err.message);
        else console.log(`BLOCKED → ${mac} (${ip})`);
    });
}

function unblockDevice(ip, mac) {
    // Remove previous drop rule
    exec(`iptables -D FORWARD -s ${ip} -j DROP || true`, (err) => {
        if (err) console.error(`Error removing DROP for ${mac} (${ip}):`, err.message);
    });

    // Allow forwarding to internet
    exec(`iptables -C FORWARD -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -m state --state RELATED,ESTABLISHED -j ACCEPT || \
          iptables -I FORWARD 1 -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -m state --state RELATED,ESTABLISHED -j ACCEPT`,
        (err) => {
            if (err) console.error(`Error allowing ${mac} (${ip}):`, err.message);
            else console.log(`UNBLOCKED → ${mac} (${ip})`);
        }
    );
}

// ───────── UPDATE ARP CACHE ─────────
let arpCache = {};

setInterval(() => {
    exec(`arp -i ${DOWNSTREAM_IFACE} -a`, (err, stdout) => {
        if (err) return;

        stdout.split("\n").forEach(line => {
            const match = line.match(/\(([\d.]+)\)\sat\s([0-9A-Fa-f:]+)/);
            if (!match) return;

            const ip = match[1];
            const mac = match[2].toUpperCase();
            arpCache[ip] = mac;
        });
    });
}, 5000); // every 5 seconds

// ───────── DEVICE HANDLER ─────────
async function handleDevice(ip, mac) {
    // Skip gateway
    if (ip === GATEWAY_IP) return;

    // Anti-hotspot sharing: block if MAC already in session
    if (sessionStore.has(mac)) {
        blockDevice(ip, mac);
        return;
    }

    // New device → add to session
    sessionStore.set(mac, { ip, connectedAt: Date.now() });

    // Check subscription status
    try {
        const res = await axios.get(`${CHECK_API}?mac=${encodeURIComponent(mac)}&current_time=${Math.floor(Date.now()/1000)}`);
        const { sub_status } = res.data;

        if (sub_status === "active") {
            unblockDevice(ip, mac);
        } else {
            blockDevice(ip, mac);
        }
    } catch (err) {
        console.error(`API error for ${mac} (${ip}):`, err.message);
        blockDevice(ip, mac); // fail-safe
    }
}

// ───────── MAIN LOOP ─────────
setInterval(() => {
    for (const ip in arpCache) {
        const mac = arpCache[ip];
        handleDevice(ip, mac);
    }
}, 10000); // every 10 seconds

// ───────── START MESSAGE ─────────
console.log("WiFi Manager running... Anti-hotspot sharing enabled, subscription check active.");