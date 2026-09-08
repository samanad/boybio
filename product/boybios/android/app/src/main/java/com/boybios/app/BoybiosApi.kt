package com.boybios.app

import android.content.Context
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody
import org.json.JSONObject

class BoybiosApi(private val context: Context) {
    private val json = "application/json; charset=utf-8".toMediaType()
    private val prefs = context.getSharedPreferences("boybios", Context.MODE_PRIVATE)

    var apiKey: String?
        get() = prefs.getString("api_key", null)
        set(value) { prefs.edit().putString("api_key", value).apply() }

    fun register(name: String, email: String, password: String): JSONObject {
        return post("/v1/register", JSONObject()
            .put("name", name)
            .put("email", email)
            .put("password", password))
    }

    fun login(email: String, password: String, twofa: String = ""): JSONObject {
        val body = JSONObject().put("email", email).put("password", password)
        if (twofa.isNotBlank()) body.put("twofa_token", twofa)
        return post("/v1/login", body)
    }

    fun account(): JSONObject = request("GET", "/v1/account")

    fun save(body: JSONObject): JSONObject = request("PATCH", "/v1/account", body)

    fun logout() {
        apiKey = null
    }

    private fun post(path: String, body: JSONObject) = request("POST", path, body)

    private fun request(method: String, path: String, body: JSONObject? = null): JSONObject {
        val builder = Request.Builder().url(CloudflareClient.url(path))
        apiKey?.let { builder.header("Authorization", "Bearer $it") }
        if (body != null) {
            builder.method(method, body.toString().toRequestBody(json))
        } else {
            builder.method(method, null)
        }
        CloudflareClient.http.newCall(builder.build()).execute().use { response ->
            val text = response.body?.string().orEmpty()
            return try {
                JSONObject(text)
            } catch (_: Exception) {
                JSONObject().put("ok", false).put("error", text.ifBlank { "http_${response.code}" })
            }
        }
    }
}
