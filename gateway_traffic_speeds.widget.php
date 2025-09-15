<?php
/*
 * gateway_speed.widget.php
 * Shows per-gateway interface speeds using ifstats.php
 */

require_once("guiconfig.inc");
require_once("pfsense-utils.inc");
require_once("functions.inc");
require_once("gwlb.inc");
require_once("interfaces.inc");

$gateways = get_gateways();
$interfaces = [];
$realinterfaces = [];
$gateway_info = [];

foreach ($gateways as $gw) {
    if (!empty($gw['friendlyiface']) && !empty($gw['interface'])) {
        $if = $gw['friendlyiface'];
        $interfaces[] = $if;
        $realinterfaces[] = $gw['interface'];

        $gateway_info[$if] = [
            'name' => $gw['friendlyifdescr'] ?? $if,
            'interface' => $gw['interface'],
            'gateway' => $gw['gateway']
        ];
    }
}
?>

<table id="gateway-table" class="table table-striped table-condensed">
    <thead>
        <tr>
            <th>Gateway</th>
            <th>Download</th>
            <th>Upload</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($interfaces as $if): ?>
        <tr data-if="<?=htmlspecialchars($if)?>">
            <td>
                <strong><?=htmlspecialchars($gateway_info[$if]['name'])?></strong><br>
                <small><?=htmlspecialchars($gateway_info[$if]['interface'])?> - <?=htmlspecialchars($gateway_info[$if]['gateway'])?></small>
            </td>
            <td class="download">Loading...</td>
            <td class="upload">Loading...</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script type="text/javascript">
var interfaces = <?=json_encode($interfaces)?>;
var realinterfaces = <?=json_encode($realinterfaces)?>;
var lastValues = {};
var lastServerTime = null;
var WRAP32 = 4294967296;

function formatSpeed(bitsPerSec) {
    if (bitsPerSec < 1000) return bitsPerSec.toFixed(0) + " bps";
    if (bitsPerSec < 1_000_000) return (bitsPerSec/1000).toFixed(1) + " Kbps";
    if (bitsPerSec < 1_000_000_000) return (bitsPerSec/1_000_000).toFixed(2) + " Mbps";
    return (bitsPerSec/1_000_000_000).toFixed(2) + " Gbps";
}

function refreshSpeeds() {
    if (interfaces.length === 0) return;

    $.ajax({
        url: "ifstats.php",
        type: "POST",
        dataType: "json",
        data: { if: interfaces.join("|"), realif: realinterfaces.join("|") },
        success: function(json) {
            let serverTime = null;
            for (const k in json) {
                if (json[k] && json[k][0] && json[k][0].values) {
                    serverTime = json[k][0].values[0];
                    break;
                }
            }
            if (serverTime === null) return;

            let dt = lastServerTime ? serverTime - lastServerTime : 0;
            lastServerTime = serverTime;

            for (const ifname in json) {
                if (!json.hasOwnProperty(ifname)) continue;
                const inBytes = json[ifname][0].values[1];
                const outBytes = json[ifname][1].values[1];

                if (inBytes === null || outBytes === null) {
                    $("#gateway-table tr[data-if='"+ifname+"'] .download").text("N/A");
                    $("#gateway-table tr[data-if='"+ifname+"'] .upload").text("N/A");
                    continue;
                }

                if (lastValues[ifname] && dt > 0) {
                    let deltaIn = inBytes - lastValues[ifname].in;
                    let deltaOut = outBytes - lastValues[ifname].out;
                    if (deltaIn < 0) deltaIn += WRAP32;
                    if (deltaOut < 0) deltaOut += WRAP32;

                    let inSpeed = (deltaIn * 8) / dt;  // convert to bits/sec
                    let outSpeed = (deltaOut * 8) / dt;

                    $("#gateway-table tr[data-if='"+ifname+"'] .download").text(formatSpeed(inSpeed));
                    $("#gateway-table tr[data-if='"+ifname+"'] .upload").text(formatSpeed(outSpeed));
                }
                lastValues[ifname] = { in: inBytes, out: outBytes };
            }
        }
    });
}

setInterval(refreshSpeeds, 2000);
events.push(refreshSpeeds);
</script>
