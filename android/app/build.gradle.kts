plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

val apiBaseUrl = providers.gradleProperty("LUZICITY_API_URL").orElse("https://luzicity.com").get()
val webBaseUrl = providers.gradleProperty("LUZICITY_WEB_URL").orElse(apiBaseUrl).get()
fun secret(name: String) = providers.gradleProperty(name).orElse("").get()

android {
    namespace = "com.luzicity.app"
    compileSdk = 35
    defaultConfig {
        applicationId = "com.luzicity.app"
        minSdk = 26
        targetSdk = 35
        versionCode = 1
        versionName = "1.0.0"
        buildConfigField("String", "API_BASE_URL", "\"$apiBaseUrl\"")
        buildConfigField("String", "WEB_BASE_URL", "\"$webBaseUrl\"")
        for (name in listOf("FIREBASE_API_KEY", "FIREBASE_APP_ID", "FIREBASE_PROJECT_ID", "FIREBASE_SENDER_ID")) {
            buildConfigField("String", name, "\"${secret(name)}\"")
        }
    }
    buildFeatures { buildConfig = true }
    buildTypes {
        release {
            isMinifyEnabled = true
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
        }
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.15.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
    implementation("com.google.android.material:material:1.12.0")
    implementation("com.google.firebase:firebase-messaging:24.1.0")
}
