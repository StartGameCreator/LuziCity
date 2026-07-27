package com.luzicity.app

import android.content.Intent
import android.os.Bundle
import android.text.InputType
import android.widget.Button
import android.widget.EditText
import android.widget.LinearLayout
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import org.json.JSONArray
import org.json.JSONObject

class LoginActivity : AppCompatActivity() {
    override fun onCreate(state: Bundle?) {
        super.onCreate(state)
        val email = EditText(this).apply { hint = getString(R.string.email); inputType = InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS }
        val password = EditText(this).apply { hint = getString(R.string.password); inputType = InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD }
        val button = Button(this).apply { text = getString(R.string.login) }
        setContentView(LinearLayout(this).apply {
            orientation = LinearLayout.VERTICAL; setPadding(48, 96, 48, 48)
            addView(email); addView(password); addView(button)
        })
        button.setOnClickListener {
            button.isEnabled = false
            Thread {
                runCatching {
                    ApiClient.request("/mobile/auth/tokens", "POST", body = JSONObject()
                        .put("email", email.text.toString()).put("password", password.text.toString())
                        .put("name", "Android").put("abilities", JSONArray(listOf("mobile:read", "mobile:write"))))
                }.onSuccess { (status, json) ->
                    runOnUiThread {
                        button.isEnabled = true
                        if (status == 201) {
                            TokenStore.save(this, json.getString("token"))
                            startActivity(Intent(this, MainActivity::class.java)); finish()
                        } else Toast.makeText(this, json.optString("message", "Não foi possível entrar."), Toast.LENGTH_LONG).show()
                    }
                }.onFailure { runOnUiThread { button.isEnabled = true; Toast.makeText(this, "Sem conexão.", Toast.LENGTH_LONG).show() } }
            }.start()
        }
    }
}
