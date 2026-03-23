// ────────────────────────────────────────────────
// WiFi Manager (Subscription + Anti-Hotspot Sharing)
// Maintained by NEWDICH TECHNOLOGY
// ────────────────────────────────────────────────

const { exec } = require('child_process');
const axios = require('axios');

// ───────── CONFIG ─────────
const DOWNSTREAM_IFACE = "eth0";       // Hotspot interface
const UPSTREAM_IFACE   = "wlan0";      // Internet interface
const GATEWAY_IP       = "192.168.200.1";
const SUBNET           = "192.168.200.0/24";

// Local API
const CHECK_API = "http://192.168.200.1:8080/newdichlan/ansofra/api/checkpaid";

// Session store (MAC → session)
const sessionStore = new Map();

// ARP cache (IP → MAC)
let arpCache = {};


// ────────────────────────────────────────────────
// INITIAL IPTABLES SETUP
// ────────────────────────────────────────────────
function setupIptables() {
    console.log("Resetting iptables...");

    exec("iptables -F");
    exec("iptables -t nat -F");

    // Allow established connections
    exec("iptables -A FORWARD -m state --state RELATED,ESTABLISHED -j ACCEPT");

    // NAT for internet access
    exec(`iptables -t nat -A POSTROUTING -o ${UPSTREAM_IFACE} -j MASQUERADE`);

    console.log("iptables ready.");
}

setupIptables();


// ────────────────────────────────────────────────
// BLOCK DEVICE
// ────────────────────────────────────────────────
function blockDevice(ip, mac) {
    exec(`iptables -C FORWARD -s ${ip} -j DROP || iptables -A FORWARD -s ${ip} -j DROP`,
        (err) => {
            if (err) console.error(`Block error ${mac} (${ip}):`, err.message);
            else console.log(`BLOCKED → ${mac} (${ip})`);
        }
    );
}


// ────────────────────────────────────────────────
// UNBLOCK DEVICE
// ────────────────────────────────────────────────
function unblockDevice(ip, mac) {

    // Remove DROP rule
    exec(`iptables -D FORWARD -s ${ip} -j DROP || true`);

    // Allow full internet access (IMPORTANT FIX)
    exec(`iptables -C FORWARD -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT || \
          iptables -I FORWARD 1 -s ${ip} -i ${DOWNSTREAM_IFACE} -o ${UPSTREAM_IFACE} -j ACCEPT`,
        (err) => {
            if (err) console.error(`Unblock error ${mac} (${ip}):`, err.message);
            else console.log(`UNBLOCKED → ${mac} (${ip})`);
        }
    );
}


// ────────────────────────────────────────────────
// UPDATE ARP CACHE
// ────────────────────────────────────────────────
function updateArpCache() {
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
}

setInterval(updateArpCache, 5000);


// ────────────────────────────────────────────────
// HANDLE DEVICE
// ────────────────────────────────────────────────
async function handleDevice(ip, mac) {

    if (ip === GATEWAY_IP) return;

    const existing = sessionStore.get(mac);

    // Detect hotspot sharing (same MAC, different IP)
    if (existing && existing.ip !== ip) {
        console.log(`HOTSPOT SHARING DETECTED → ${mac}`);
        blockDevice(ip, mac);
        return;
    }

    // Update session
    sessionStore.set(mac, {
        ip,
        connectedAt: Date.now()
    });

    // Check subscription
    try {
        const res = await axios.get(
            `${CHECK_API}?mac=${encodeURIComponent(mac)}&current_time=${Math.floor(Date.now()/1000)}`
        );

        const sub_status = res.data.sub_status;

        if (sub_status === "active") {
            unblockDevice(ip, mac);
        } else {
            blockDevice(ip, mac);
        }

    } catch (err) {
        console.error(`API ERROR → ${mac} (${ip}):`, err.message);
        blockDevice(ip, mac);
    }
}


// ────────────────────────────────────────────────
// MAIN LOOP
// ────────────────────────────────────────────────
setInterval(() => {
    for (const ip in arpCache) {
        const mac = arpCache[ip];
        handleDevice(ip, mac);
    }
}, 10000);


// ────────────────────────────────────────────────
// START MESSAGE
// ────────────────────────────────────────────────
console.log("WiFi Manager running...");
console.log("Subscription control + Anti-hotspot sharing ACTIVE");