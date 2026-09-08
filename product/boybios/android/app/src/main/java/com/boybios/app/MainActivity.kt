package com.boybios.app

import android.app.Activity
import android.content.Intent
import android.os.Bundle

class MainActivity : Activity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val api = BoybiosApi(this)
        startActivity(
            Intent(this, if (api.apiKey.isNullOrBlank()) AuthActivity::class.java else SettingsActivity::class.java)
        )
        finish()
    }
}
