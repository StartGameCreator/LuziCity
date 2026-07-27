package com.luzicity.app

import android.content.Context

object TokenStore {
    fun get(context: Context): String? = context.getSharedPreferences("auth", Context.MODE_PRIVATE).getString("token", null)
    fun save(context: Context, token: String) = context.getSharedPreferences("auth", Context.MODE_PRIVATE).edit().putString("token", token).apply()
}
