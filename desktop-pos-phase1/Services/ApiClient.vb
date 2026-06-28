Imports System.Net.Http
Imports System.Net.Http.Headers
Imports System.Text
Imports System.Text.Json
Imports MalrajaPOS.Phase1.Models

Namespace Services
    Public Class ApiClient
        Private ReadOnly _httpClient As HttpClient
        Private ReadOnly _jsonOptions As JsonSerializerOptions

        Public Sub New(baseUrl As String)
            If String.IsNullOrWhiteSpace(baseUrl) Then
                Throw New ArgumentException("Server URL is required.")
            End If

            Dim normalized = baseUrl.TrimEnd("/"c)
            _httpClient = New HttpClient() With {
                .BaseAddress = New Uri($"{normalized}/api/")
            }

            _jsonOptions = New JsonSerializerOptions With {
                .PropertyNameCaseInsensitive = True
            }
        End Sub

        Public Async Function LoginAsync(email As String, password As String, deviceId As String) As Task(Of LoginResponseData)
            If String.IsNullOrWhiteSpace(email) OrElse String.IsNullOrWhiteSpace(password) OrElse String.IsNullOrWhiteSpace(deviceId) Then
                Throw New InvalidOperationException("Email, password, and device id are required.")
            End If

            Dim payload = New LoginRequest With {
                .email = email,
                .password = password,
                .device_id = deviceId
            }

            Dim content = New StringContent(JsonSerializer.Serialize(payload), Encoding.UTF8, "application/json")
            Dim response = Await _httpClient.PostAsync("login", content)
            Dim body = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                Throw New InvalidOperationException($"Server error: {response.StatusCode}")
            End If

            Dim envelope = JsonSerializer.Deserialize(Of ApiEnvelopeRaw)(body, _jsonOptions)
            If envelope Is Nothing Then
                Throw New InvalidOperationException("Invalid server response.")
            End If
            If Not envelope.Success Then
                Throw New InvalidOperationException(If(envelope.Message, "Login failed."))
            End If

            If envelope.Data.ValueKind <> JsonValueKind.Object Then
                Throw New InvalidOperationException("Login response missing user data.")
            End If

            Dim loginData = JsonSerializer.Deserialize(Of LoginResponseData)(envelope.Data.GetRawText(), _jsonOptions)
            If loginData Is Nothing OrElse String.IsNullOrWhiteSpace(loginData.Token) Then
                Throw New InvalidOperationException("Login token not found in response.")
            End If

            Return loginData
        End Function

        Public Async Function GetProductsAsync(accessToken As String) As Task(Of List(Of ProductItem))
            If String.IsNullOrWhiteSpace(accessToken) Then
                Throw New InvalidOperationException("Access token is required.")
            End If

            _httpClient.DefaultRequestHeaders.Authorization = New AuthenticationHeaderValue("Bearer", accessToken)
            Dim response = Await _httpClient.GetAsync("get-product")
            Dim body = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                Throw New InvalidOperationException($"Server error: {response.StatusCode}")
            End If

            Dim envelope = JsonSerializer.Deserialize(Of ApiEnvelopeRaw)(body, _jsonOptions)
            If envelope Is Nothing Then
                Throw New InvalidOperationException("Invalid server response.")
            End If
            If Not envelope.Success Then
                Throw New InvalidOperationException(If(envelope.Message, "Product fetch failed."))
            End If
            If envelope.Data.ValueKind <> JsonValueKind.Object Then
                Return New List(Of ProductItem)
            End If

            Dim data = JsonSerializer.Deserialize(Of ProductResponseData)(envelope.Data.GetRawText(), _jsonOptions)
            Return If(data?.Products, New List(Of ProductItem))
        End Function
    End Class
End Namespace
