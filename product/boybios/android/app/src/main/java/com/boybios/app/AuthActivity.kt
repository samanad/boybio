package com.boybios.app

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import org.json.JSONObject
import java.util.concurrent.Executors

class AuthActivity : Activity() {
    private val io = Executors.newSingleThreadExecutor()
    private val main = Handler(Looper.getMainLooper())
    private lateinit var api: BoybiosApi

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_auth)
        api = BoybiosApi(this)
        findViewById<Button>(R.id.login).setOnClickListener { runAuth(false) }
        findViewById<Button>(R.id.register).setOnClickListener { runAuth(true) }
    }

    private fun runAuth(register: Boolean) {
        val name = findViewById<EditText>(R.id.name).text.toString().trim()
        val email = findViewById<EditText>(R.id.email).text.toString().trim()
        val password = findViewById<EditText>(R.id.password).text.toString()
        val twofa = findViewById<EditText>(R.id.twofa).text.toString()
        val status = findViewById<TextView>(R.id.status)
        status.text = "Please wait…"
        io.execute {
            val result = runCatching {
                if (register) api.register(name, email, password) else api.login(email, password, twofa)
            }.getOrElse { JSONObject().put("ok", false).put("error", it.message) }
            main.post {
                when {
                    result.optBoolean("needs_twofa") -> status.text = "Enter your 2FA code and sign in again."
                    result.optBoolean("needs_email_confirmation") -> status.text = "Check your email to confirm."
                    !result.optBoolean("ok") -> status.text = formatError(result)
                    else -> {
                        val key = result.optJSONObject("user")?.optString("api_key").orEmpty()
                        if (key.isBlank()) {
                            status.text = "No session returned."
                        } else {
                            api.apiKey = key
                            startActivity(Intent(this, SettingsActivity::class.java))
                            finish()
                        }
                    }
                }
            }
        }
    }

    private fun formatError(result: JSONObject): String {
        val fields = result.optJSONObject("fields")
        if (fields != null && fields.length() > 0) {
            val map = mapOf(
                "invalid_name" to "Enter a name",
                "invalid_email" to "Enter a valid email",
                "email_exists" to "This email is already registered",
                "email_aliases_not_allowed" to "This email is not allowed",
                "invalid_password" to "Password must be 6 to 64 characters",
                "invalid_current_password" to "Current password is wrong",
            )
            val parts = mutableListOf<String>()
            fields.keys().forEach { key ->
                val code = fields.optString(key)
                parts += map[code] ?: code
            }
            if (parts.isNotEmpty()) return parts.joinToString(". ")
        }
        val error = result.optString("error", "failed")
        if (error.contains('<') || error.length > 120) {
            return "Could not create the account. Try again."
        }
        return error
    }
}
