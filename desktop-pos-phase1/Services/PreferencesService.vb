Imports System.IO
Imports System.Text.Json
Imports MalrajaPOS.Phase1.Models

Namespace Services
    Public Class PreferencesService
        Private ReadOnly _settingsPath As String

        Public Sub New()
            Dim appDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData), "MalrajaPOS")
            Directory.CreateDirectory(appDir)
            _settingsPath = Path.Combine(appDir, "phase1-settings.json")
        End Sub

        Public Function Load() As AppPreferences
            If Not File.Exists(_settingsPath) Then
                Return New AppPreferences()
            End If

            Dim json = File.ReadAllText(_settingsPath)
            Dim data = JsonSerializer.Deserialize(Of AppPreferences)(json)
            Return If(data, New AppPreferences())
        End Function

        Public Sub Save(data As AppPreferences)
            Dim json = JsonSerializer.Serialize(data, New JsonSerializerOptions With {.WriteIndented = True})
            File.WriteAllText(_settingsPath, json)
        End Sub
    End Class
End Namespace
