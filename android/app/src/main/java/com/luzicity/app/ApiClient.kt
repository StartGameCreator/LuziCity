package com.luzicity.app

import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

object ApiClient {
    fun request(path: String, method: String = "GET", token: String? = null, body: JSONObject? = null): Pair<Int, JSONObject> {
        val connection = URL(BuildConfig.API_BASE_URL.trimEnd('/') + "/api/v1" + path).openConnection() as HttpURLConnection
        connection.requestMethod = method
        connection.connectTimeout = 10_000
        connection.readTimeout = 15_000
        connection.setRequestProperty("Accept", "application/json")
        connection.setRequestProperty("Content-Type", "application/json")
        token?.let { connection.setRequestProperty("Authorization", "Bearer $it") }
        body?.let {
            connection.doOutput = true
            connection.outputStream.use { output -> output.write(it.toString().toByteArray()) }
        }
        val status = connection.responseCode
        val stream = if (status in 200..299) connection.inputStream else connection.errorStream
        val text = stream?.bufferedReader()?.use { it.readText() }.orEmpty()
        return status to if (text.isBlank()) JSONObject() else JSONObject(text)
    }
}
