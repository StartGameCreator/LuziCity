package com.luzicity.app

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import org.json.JSONObject

class LuziCityMessagingService : FirebaseMessagingService() {
    override fun onNewToken(token: String) = register(this, token)

    override fun onMessageReceived(message: RemoteMessage) {
        val manager = getSystemService(NotificationManager::class.java)
        if (Build.VERSION.SDK_INT >= 26) {
            manager.createNotificationChannel(NotificationChannel("news", "Notícias", NotificationManager.IMPORTANCE_DEFAULT))
        }
        val url = message.data["url"] ?: BuildConfig.WEB_BASE_URL
        val intent = Intent(this, MainActivity::class.java).setData(android.net.Uri.parse(url))
        val pending = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
        manager.notify(message.messageId?.hashCode() ?: System.currentTimeMillis().toInt(),
            NotificationCompat.Builder(this, "news").setSmallIcon(R.drawable.ic_launcher)
                .setContentTitle(message.notification?.title ?: "LuziCity")
                .setContentText(message.notification?.body ?: "").setAutoCancel(true).setContentIntent(pending).build())
    }

    companion object {
        fun register(context: Context, firebaseToken: String) {
            val bearer = TokenStore.get(context) ?: return
            Thread {
                runCatching {
                    ApiClient.request("/mobile/notifications/devices", "POST", bearer, JSONObject()
                        .put("token", firebaseToken).put("device_name", Build.MODEL).put("platform", "android"))
                }
            }.start()
        }
    }
}
