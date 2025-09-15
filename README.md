# pfsense Gateway Traffic Speeds Widget
This pfSense dashboard widget provides real-time monitoring of gateway throughput across all configured WAN connections. It displays current inbound and outbound traffic for each gateway, refreshing every 2 seconds to give administrators up-to-date visibility into multi-WAN performance.

✅ **Tested on:**  
pfSense **2.8.0-RELEASE (amd64)**  
Built on Thu May 22 07:12:00 PST 2025  
FreeBSD **15.0-CURRENT**

## Features
- Displays the speed of each gateway when using multi-WAN on pfSense, updating every 2 seconds.
- Speeds are automatically scaled to kilobits, megabits, or gigabits per second for readability.

## Screenshot

![Screenshot](https://github.com/woots29/pfsense-gateway-traffic-speeds-widget/blob/main/gateway_speed_widget.JPG?raw=true)

## Installation
copy `gateway_traffic_speeds.widget.php` on /usr/local/www/widgets/widgets of pFsense
