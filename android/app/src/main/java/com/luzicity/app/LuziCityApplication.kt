package com.luzicity.app

import android.app.Application
import com.google.firebase.FirebaseApp
import com.google.firebase.FirebaseOptions

class LuziCityApplication : Application() {
    override fun onCreate() {
        super.onCreate()
        if (FirebaseApp.getApps(this).isEmpty() && BuildConfig.FIREBASE_API_KEY.isNotBlank()) {
            FirebaseApp.initializeApp(this, FirebaseOptions.Builder()
                .setApiKey(BuildConfig.FIREBASE_API_KEY).setApplicationId(BuildConfig.FIREBASE_APP_ID)
                .setProjectId(BuildConfig.FIREBASE_PROJECT_ID).setGcmSenderId(BuildConfig.FIREBASE_SENDER_ID).build())
        }
    }
}
