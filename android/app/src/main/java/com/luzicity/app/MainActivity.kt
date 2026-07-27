package com.luzicity.app

import android.Manifest
import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.webkit.WebResourceRequest
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.FirebaseApp
import com.google.firebase.messaging.FirebaseMessaging

class MainActivity : AppCompatActivity() {
    private lateinit var webView: WebView

    override fun onCreate(state: Bundle?) {
        super.onCreate(state)
        if (TokenStore.get(this) == null) {
            startActivity(Intent(this, LoginActivity::class.java)); finish(); return
        }
        webView = WebView(this)
        setContentView(webView)
        webView.settings.javaScriptEnabled = true
        webView.settings.domStorageEnabled = true
        webView.settings.cacheMode = android.webkit.WebSettings.LOAD_DEFAULT
        webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(view: WebView, request: WebResourceRequest): Boolean {
                val appHost = Uri.parse(BuildConfig.WEB_BASE_URL).host
                if (request.url.host == appHost) return false
                startActivity(Intent(Intent.ACTION_VIEW, request.url))
                return true
            }

            override fun onReceivedError(view: WebView, request: WebResourceRequest, error: android.webkit.WebResourceError) {
                if (request.isForMainFrame) {
                    view.settings.cacheMode = android.webkit.WebSettings.LOAD_CACHE_ELSE_NETWORK
                    view.loadUrl("file:///android_asset/offline.html")
                }
            }
        }
        loadDeepLink(intent)
        if (Build.VERSION.SDK_INT >= 33) requestPermissions(arrayOf(Manifest.permission.POST_NOTIFICATIONS), 42)
        if (FirebaseApp.getApps(this).isNotEmpty()) {
            FirebaseMessaging.getInstance().token.addOnSuccessListener { LuziCityMessagingService.register(this, it) }
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        loadDeepLink(intent)
    }

    private fun loadDeepLink(intent: Intent) {
        val uri = intent.data
        val target = when {
            uri?.scheme == "luzicity" && uri.host == "news" ->
                BuildConfig.WEB_BASE_URL.trimEnd('/') + "/noticias/" + Uri.encode(uri.lastPathSegment ?: "")
            uri?.scheme == "https" -> uri.toString()
            else -> BuildConfig.WEB_BASE_URL
        }
        webView.loadUrl(target)
    }

    override fun onBackPressed() {
        if (webView.canGoBack()) webView.goBack() else super.onBackPressed()
    }
}
