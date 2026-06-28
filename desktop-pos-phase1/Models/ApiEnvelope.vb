Imports System.Text.Json.Serialization
Imports System.Text.Json

Namespace Models
    Public Class ApiEnvelope(Of T)
        <JsonPropertyName("success")>
        Public Property Success As Boolean

        <JsonPropertyName("message")>
        Public Property Message As String

        <JsonPropertyName("data")>
        Public Property Data As T
    End Class

    Public Class ApiEnvelopeRaw
        <JsonPropertyName("success")>
        Public Property Success As Boolean

        <JsonPropertyName("message")>
        Public Property Message As String

        <JsonPropertyName("data")>
        Public Property Data As JsonElement
    End Class
End Namespace
