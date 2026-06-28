Imports System.Text.Json.Serialization

Namespace Models
    Public Class LoginResponseData
        <JsonPropertyName("user_id")>
        Public Property UserId As Integer

        <JsonPropertyName("token")>
        Public Property Token As String

        <JsonPropertyName("email")>
        Public Property Email As String

        <JsonPropertyName("name")>
        Public Property Name As String

        <JsonPropertyName("user_type")>
        Public Property UserType As String
    End Class

    Public Class LoginRequest
        Public Property email As String
        Public Property password As String
        Public Property device_id As String
    End Class
End Namespace
