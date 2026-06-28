Imports MalrajaPOS.Phase1.Models
Imports MalrajaPOS.Phase1.Services
Imports System.Drawing
Imports System.Data

Public Class Form1
    Private _apiClient As ApiClient
    Private _accessToken As String = String.Empty
    Private ReadOnly _preferencesService As PreferencesService
    Private ReadOnly _productsTable As DataTable
    Private ReadOnly _cartTable As DataTable

    Public Sub New()
        InitializeComponent()
        _preferencesService = New PreferencesService()
        ApplyLegacyStyle()

        _productsTable = New DataTable()
        _productsTable.Columns.Add("Id", GetType(Integer))
        _productsTable.Columns.Add("Name", GetType(String))
        dgvProducts.DataSource = _productsTable

        _cartTable = New DataTable()
        _cartTable.Columns.Add("ProductId", GetType(Integer))
        _cartTable.Columns.Add("Name", GetType(String))
        _cartTable.Columns.Add("Qty", GetType(Decimal))
        _cartTable.Columns.Add("Rate", GetType(Decimal))
        _cartTable.Columns.Add("Amount", GetType(Decimal))
        dgvCart.DataSource = _cartTable

        LoadPreferences()
        UpdateCartTotal()
        SetLoggedOutState()
    End Sub

    Private Sub ApplyLegacyStyle()
        BackColor = Color.White
        Text = "Super SalesSoft Billing - POS (Phase 1.1)"
        ClientSize = New Size(980, 560)

        Dim existingTitle = Controls.Find("lblPageTitle", False).FirstOrDefault()
        If existingTitle IsNot Nothing Then
            Controls.Remove(existingTitle)
        End If
        Dim existingBanner = Controls.Find("pnlTopBanner", False).FirstOrDefault()
        If existingBanner IsNot Nothing Then
            Controls.Remove(existingBanner)
        End If

        Dim topBanner As New Panel() With {
            .Name = "pnlTopBanner",
            .Location = New Point(0, 0),
            .Size = New Size(ClientSize.Width, 85),
            .BackColor = Color.Gainsboro,
            .Anchor = AnchorStyles.Top Or AnchorStyles.Left Or AnchorStyles.Right
        }
        Controls.Add(topBanner)

        Dim titleLabel As New Label() With {
            .Name = "lblPageTitle",
            .AutoSize = True,
            .Text = "MANAGE PRODUCTS / POS CART",
            .Font = New Font("Comic Sans MS", 16.0F, FontStyle.Bold),
            .ForeColor = Color.Blue,
            .Location = New Point(250, 24)
        }
        topBanner.Controls.Add(titleLabel)

        Dim fieldFont As New Font("Segoe UI", 10.0F, FontStyle.Regular)
        lblServerUrl.Font = fieldFont
        lblEmail.Font = fieldFont
        lblPassword.Font = fieldFont
        lblDeviceId.Font = fieldFont
        lblSearch.Font = fieldFont
        lblQty.Font = fieldFont
        lblRate.Font = fieldFont
        lblStatus.Font = fieldFont
        lblCartTotal.Font = fieldFont

        Dim buttonFont As New Font("Segoe UI Semibold", 9.5F, FontStyle.Bold)
        For Each btn As Button In New Button() {btnLogin, btnLoadProducts, btnLogout, btnAddToCart, btnRemoveCartItem, btnClearCart}
            btn.Font = buttonFont
            btn.FlatStyle = FlatStyle.Standard
            btn.BackColor = SystemColors.Control
        Next

        dgvProducts.BackgroundColor = Color.White
        dgvCart.BackgroundColor = Color.White
        dgvProducts.DefaultCellStyle.Font = New Font("Segoe UI", 10.0F, FontStyle.Regular)
        dgvProducts.ColumnHeadersDefaultCellStyle.Font = New Font("Segoe UI", 10.0F, FontStyle.Bold)
        dgvCart.DefaultCellStyle.Font = New Font("Segoe UI", 10.0F, FontStyle.Regular)
        dgvCart.ColumnHeadersDefaultCellStyle.Font = New Font("Segoe UI", 10.0F, FontStyle.Bold)

        ' Legacy-inspired layout: left controls, right data grids.
        lblServerUrl.Location = New Point(16, 100)
        txtServerUrl.Location = New Point(112, 98)
        txtServerUrl.Size = New Size(280, 23)
        lblEmail.Location = New Point(16, 132)
        txtEmail.Location = New Point(112, 130)
        txtEmail.Size = New Size(280, 23)
        lblPassword.Location = New Point(16, 164)
        txtPassword.Location = New Point(112, 162)
        txtPassword.Size = New Size(280, 23)
        lblDeviceId.Location = New Point(16, 196)
        txtDeviceId.Location = New Point(112, 194)
        txtDeviceId.Size = New Size(280, 23)

        btnLogin.Location = New Point(16, 226)
        btnLogin.Size = New Size(120, 32)
        btnLoadProducts.Location = New Point(142, 226)
        btnLoadProducts.Size = New Size(130, 32)
        btnLogout.Location = New Point(278, 226)
        btnLogout.Size = New Size(114, 32)

        lblSearch.Location = New Point(16, 272)
        txtSearch.Location = New Point(112, 269)
        txtSearch.Size = New Size(280, 23)
        lblQty.Location = New Point(16, 304)
        nudQty.Location = New Point(112, 302)
        nudQty.Size = New Size(120, 23)
        lblRate.Location = New Point(240, 304)
        txtRate.Location = New Point(277, 302)
        txtRate.Size = New Size(115, 23)
        btnAddToCart.Location = New Point(16, 334)
        btnAddToCart.Size = New Size(376, 33)

        dgvProducts.Location = New Point(410, 98)
        dgvProducts.Size = New Size(554, 210)
        dgvCart.Location = New Point(410, 314)
        dgvCart.Size = New Size(554, 196)
        btnRemoveCartItem.Location = New Point(410, 517)
        btnClearCart.Location = New Point(525, 517)
        lblCartTotal.Location = New Point(640, 521)
        lblStatus.Location = New Point(16, 378)
        lblStatus.MaximumSize = New Size(376, 0)

        StartPosition = FormStartPosition.CenterScreen
    End Sub

    Private Async Sub btnLogin_Click(sender As Object, e As EventArgs) Handles btnLogin.Click
        ToggleBusyState(True)
        Try
            _apiClient = New ApiClient(txtServerUrl.Text.Trim())
            Dim result = Await _apiClient.LoginAsync(txtEmail.Text.Trim(), txtPassword.Text, txtDeviceId.Text.Trim())

            _accessToken = result.Token
            lblStatus.Text = $"Logged in as: {result.Name} ({result.Email})"
            btnLoadProducts.Enabled = True
            btnLogout.Enabled = True
            SavePreferences()
            MessageBox.Show("Login successful.", "Success", MessageBoxButtons.OK, MessageBoxIcon.Information)
        Catch ex As Exception
            _accessToken = String.Empty
            SetLoggedOutState()
            lblStatus.Text = $"Login failed: {ex.Message}"
            MessageBox.Show(ex.ToString(), "Login Error (details)", MessageBoxButtons.OK, MessageBoxIcon.Error)
        Finally
            ToggleBusyState(False)
        End Try
    End Sub

    Private Async Sub btnLoadProducts_Click(sender As Object, e As EventArgs) Handles btnLoadProducts.Click
        If String.IsNullOrWhiteSpace(_accessToken) Then
            MessageBox.Show("Please login first.", "Info", MessageBoxButtons.OK, MessageBoxIcon.Information)
            Return
        End If

        ToggleBusyState(True)
        Try
            Dim products = Await _apiClient.GetProductsAsync(_accessToken)
            _productsTable.Rows.Clear()
            For Each p In products
                _productsTable.Rows.Add(p.Id, p.Text)
            Next
            txtSearch.Text = String.Empty
            lblStatus.Text = $"Loaded {products.Count} products."
            btnAddToCart.Enabled = products.Count > 0
        Catch ex As Exception
            lblStatus.Text = $"Product load failed: {ex.Message}"
            MessageBox.Show(ex.ToString(), "Error (details)", MessageBoxButtons.OK, MessageBoxIcon.Error)
        Finally
            ToggleBusyState(False)
        End Try
    End Sub

    Private Sub btnAddToCart_Click(sender As Object, e As EventArgs) Handles btnAddToCart.Click
        If dgvProducts.CurrentRow Is Nothing Then
            MessageBox.Show("Please select a product.", "Info", MessageBoxButtons.OK, MessageBoxIcon.Information)
            Return
        End If

        Dim productId = Convert.ToInt32(dgvProducts.CurrentRow.Cells("Id").Value)
        Dim productName = dgvProducts.CurrentRow.Cells("Name").Value.ToString()
        Dim qty = nudQty.Value
        Dim rate As Decimal
        If Not Decimal.TryParse(txtRate.Text, rate) Then
            MessageBox.Show("Rate should be a valid number.", "Validation", MessageBoxButtons.OK, MessageBoxIcon.Warning)
            Return
        End If

        Dim existing = _cartTable.Select($"ProductId = {productId}").FirstOrDefault()
        If existing Is Nothing Then
            _cartTable.Rows.Add(productId, productName, qty, rate, qty * rate)
        Else
            existing("Qty") = Convert.ToDecimal(existing("Qty")) + qty
            existing("Rate") = rate
            existing("Amount") = Convert.ToDecimal(existing("Qty")) * Convert.ToDecimal(existing("Rate"))
        End If

        UpdateCartTotal()
    End Sub

    Private Sub btnRemoveCartItem_Click(sender As Object, e As EventArgs) Handles btnRemoveCartItem.Click
        If dgvCart.CurrentRow Is Nothing Then
            Return
        End If
        dgvCart.Rows.RemoveAt(dgvCart.CurrentRow.Index)
        UpdateCartTotal()
    End Sub

    Private Sub btnClearCart_Click(sender As Object, e As EventArgs) Handles btnClearCart.Click
        _cartTable.Rows.Clear()
        UpdateCartTotal()
    End Sub

    Private Sub txtSearch_TextChanged(sender As Object, e As EventArgs) Handles txtSearch.TextChanged
        Dim source As DataTable = _productsTable
        If source Is Nothing Then
            Return
        End If
        Dim filterText = txtSearch.Text.Trim().Replace("'", "''")
        If String.IsNullOrWhiteSpace(filterText) Then
            source.DefaultView.RowFilter = String.Empty
        Else
            source.DefaultView.RowFilter = $"Name LIKE '%{filterText}%'"
        End If
    End Sub

    Private Sub btnLogout_Click(sender As Object, e As EventArgs) Handles btnLogout.Click
        SetLoggedOutState()
        lblStatus.Text = "Logged out."
    End Sub

    Private Sub Form1_FormClosing(sender As Object, e As FormClosingEventArgs) Handles MyBase.FormClosing
        SavePreferences()
    End Sub

    Private Sub ToggleBusyState(isBusy As Boolean)
        btnLogin.Enabled = Not isBusy
        btnLoadProducts.Enabled = Not isBusy AndAlso Not String.IsNullOrWhiteSpace(_accessToken)
        btnAddToCart.Enabled = Not isBusy AndAlso Not String.IsNullOrWhiteSpace(_accessToken) AndAlso _productsTable.Rows.Count > 0
        btnLogout.Enabled = Not isBusy AndAlso Not String.IsNullOrWhiteSpace(_accessToken)
        Cursor = If(isBusy, Cursors.WaitCursor, Cursors.Default)
    End Sub

    Private Sub UpdateCartTotal()
        Dim total As Decimal = 0D
        For Each row As DataRow In _cartTable.Rows
            total += Convert.ToDecimal(row("Amount"))
        Next
        lblCartTotal.Text = $"Total Amount: {total:0.00}"
    End Sub

    Private Sub SetLoggedOutState()
        _accessToken = String.Empty
        btnLoadProducts.Enabled = False
        btnAddToCart.Enabled = False
        btnLogout.Enabled = False
        _productsTable.Rows.Clear()
        _cartTable.Rows.Clear()
        UpdateCartTotal()
    End Sub

    Private Sub LoadPreferences()
        Dim pref = _preferencesService.Load()
        txtServerUrl.Text = If(String.IsNullOrWhiteSpace(pref.ServerUrl), "https://malraja.supersalessoft.com", pref.ServerUrl)
        txtEmail.Text = If(pref.Email, String.Empty)
        txtDeviceId.Text = If(String.IsNullOrWhiteSpace(pref.DeviceId), Environment.MachineName, pref.DeviceId)
    End Sub

    Private Sub SavePreferences()
        _preferencesService.Save(New AppPreferences With {
            .ServerUrl = txtServerUrl.Text.Trim(),
            .Email = txtEmail.Text.Trim(),
            .DeviceId = txtDeviceId.Text.Trim()
        })
    End Sub
End Class
