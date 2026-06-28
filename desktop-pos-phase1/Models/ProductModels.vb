Imports System.Text.Json.Serialization

Namespace Models
    Public Class ProductItem
        <JsonPropertyName("id")>
        Public Property Id As Integer

        <JsonPropertyName("text")>
        Public Property Text As String
    End Class

    Public Class ProductResponseData
        <JsonPropertyName("products")>
        Public Property Products As List(Of ProductItem)
    End Class
End Namespace
