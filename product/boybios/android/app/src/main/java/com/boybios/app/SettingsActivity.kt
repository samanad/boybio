package com.boybios.app

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.Button
import android.widget.CheckBox
import android.widget.EditText
import android.widget.TextView
import org.json.JSONObject
import java.util.concurrent.Executors

class SettingsActivity : Activity() {
    private val io = Executors.newSingleThreadExecutor()
    private val main = Handler(Looper.getMainLooper())
    private lateinit var api: BoybiosApi

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_settings)
        api = BoybiosApi(this)
        findViewById<Button>(R.id.save).setOnClickListener { save() }
        findViewById<Button>(R.id.logout).setOnClickListener {
            api.logout()
            startActivity(Intent(this, AuthActivity::class.java))
            finish()
        }
        load()
    }

    private fun load() {
        val status = findViewById<TextView>(R.id.status)
        status.text = "Loading…"
        io.execute {
            val result = runCatching { api.account() }.getOrElse { JSONObject().put("ok", false).put("error", it.message) }
            main.post {
                if (!result.optBoolean("ok")) {
                    status.text = result.optString("error", "failed")
                    return@post
                }
                val user = result.getJSONObject("user")
                findViewById<TextView>(R.id.header).text = "Account · ${user.optString("email")}"
                findViewById<EditText>(R.id.name).setText(user.optString("name"))
                findViewById<EditText>(R.id.email).setText(user.optString("email"))
                findViewById<EditText>(R.id.timezone).setText(user.optString("timezone"))
                findViewById<EditText>(R.id.anti).setText(user.optString("anti_phishing_code"))
                findViewById<CheckBox>(R.id.newsletter).isChecked = user.optBoolean("is_newsletter_subscribed")
                val billing = user.optJSONObject("billing")
                findViewById<EditText>(R.id.billingName).setText(billing?.optString("name"))
                findViewById<EditText>(R.id.billingAddress).setText(billing?.optString("address"))
                findViewById<EditText>(R.id.billingCity).setText(billing?.optString("city"))
                findViewById<EditText>(R.id.billingCountry).setText(billing?.optString("country"))
                status.text = "Plan: ${user.optString("plan_id")}"
            }
        }
    }

    private fun save() {
        val status = findViewById<TextView>(R.id.status)
        status.text = "Saving…"
        val body = JSONObject()
            .put("name", findViewById<EditText>(R.id.name).text.toString())
            .put("email", findViewById<EditText>(R.id.email).text.toString())
            .put("timezone", findViewById<EditText>(R.id.timezone).text.toString())
            .put("anti_phishing_code", findViewById<EditText>(R.id.anti).text.toString())
            .put("is_newsletter_subscribed", findViewById<CheckBox>(R.id.newsletter).isChecked)
            .put(
                "billing",
                JSONObject()
                    .put("type", "personal")
                    .put("name", findViewById<EditText>(R.id.billingName).text.toString())
                    .put("address", findViewById<EditText>(R.id.billingAddress).text.toString())
                    .put("city", findViewById<EditText>(R.id.billingCity).text.toString())
                    .put("country", findViewById<EditText>(R.id.billingCountry).text.toString())
            )
        val old = findViewById<EditText>(R.id.oldPassword).text.toString()
        val new = findViewById<EditText>(R.id.newPassword).text.toString()
        if (old.isNotBlank() && new.isNotBlank()) {
            body.put("old_password", old).put("new_password", new)
        }
        io.execute {
            val result = runCatching { api.save(body) }.getOrElse { JSONObject().put("ok", false).put("error", it.message) }
            main.post {
                status.text = if (result.optBoolean("ok")) "Saved." else result.optString("error", "failed")
            }
        }
    }
}
