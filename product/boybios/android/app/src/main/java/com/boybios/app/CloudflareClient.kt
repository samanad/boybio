package com.boybios.app

import okhttp3.Dns
import okhttp3.OkHttpClient
import java.net.InetAddress
import java.net.UnknownHostException
import java.util.concurrent.TimeUnit

/**
 * Talks to the boybios.com Worker on hardcoded Cloudflare anycast IPs.
 * TLS SNI and HTTP Host stay boybios.com. No DNS or 1.1.1.1 lookup.
 */
object CloudflareClient {
    private val host = BuildConfig.BOYBIOS_HOST

    private val cloudflareIpv4 = listOf(
        "104.16.0.1",
        "104.17.0.1",
        "104.18.0.1",
        "104.19.0.1",
        "104.21.0.1",
        "104.22.0.1",
        "104.24.0.1",
        "104.25.0.1",
        "172.64.0.1",
        "172.67.0.1",
        "141.101.64.1",
        "162.158.0.1",
        "188.114.96.1",
        "198.41.128.1",
        "108.162.192.1",
        "173.245.48.1",
    )

    private val pinnedDns = object : Dns {
        override fun lookup(hostname: String): List<InetAddress> {
            if (hostname != host) {
                throw UnknownHostException(hostname)
            }
            return cloudflareIpv4.map { dotted ->
                InetAddress.getByAddress(hostname, ipv4(dotted))
            }
        }
    }

    val http: OkHttpClient = OkHttpClient.Builder()
        .dns(pinnedDns)
        .connectTimeout(20, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .build()

    fun url(path: String) = "https://$host$path"

    private fun ipv4(dotted: String): ByteArray {
        val parts = dotted.split('.')
        require(parts.size == 4)
        return byteArrayOf(
            parts[0].toInt().toByte(),
            parts[1].toInt().toByte(),
            parts[2].toInt().toByte(),
            parts[3].toInt().toByte(),
        )
    }
}
