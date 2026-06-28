<Global.Microsoft.VisualBasic.CompilerServices.DesignerGenerated()>
Partial Class Form1
    Inherits System.Windows.Forms.Form

    'Form overrides dispose to clean up the component list.
    <System.Diagnostics.DebuggerNonUserCode()>
    Protected Overrides Sub Dispose(ByVal disposing As Boolean)
        Try
            If disposing AndAlso components IsNot Nothing Then
                components.Dispose()
            End If
        Finally
            MyBase.Dispose(disposing)
        End Try
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer
    Private lblServerUrl As Label
    Private txtServerUrl As TextBox
    Private lblEmail As Label
    Private txtEmail As TextBox
    Private lblPassword As Label
    Private txtPassword As TextBox
    Private lblDeviceId As Label
    Private txtDeviceId As TextBox
    Private WithEvents btnLogin As Button
    Private WithEvents btnLoadProducts As Button
    Private WithEvents btnLogout As Button
    Private lblStatus As Label
    Private dgvProducts As DataGridView
    Private lblSearch As Label
    Private WithEvents txtSearch As TextBox
    Private lblQty As Label
    Private nudQty As NumericUpDown
    Private lblRate As Label
    Private txtRate As TextBox
    Private WithEvents btnAddToCart As Button
    Private dgvCart As DataGridView
    Private WithEvents btnRemoveCartItem As Button
    Private WithEvents btnClearCart As Button
    Private lblCartTotal As Label

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    <System.Diagnostics.DebuggerStepThrough()>
    Private Sub InitializeComponent()
        components = New System.ComponentModel.Container()
        lblServerUrl = New Label()
        txtServerUrl = New TextBox()
        lblEmail = New Label()
        txtEmail = New TextBox()
        lblPassword = New Label()
        txtPassword = New TextBox()
        lblDeviceId = New Label()
        txtDeviceId = New TextBox()
        btnLogin = New Button()
        btnLoadProducts = New Button()
        btnLogout = New Button()
        lblStatus = New Label()
        dgvProducts = New DataGridView()
        lblSearch = New Label()
        txtSearch = New TextBox()
        lblQty = New Label()
        nudQty = New NumericUpDown()
        lblRate = New Label()
        txtRate = New TextBox()
        btnAddToCart = New Button()
        dgvCart = New DataGridView()
        btnRemoveCartItem = New Button()
        btnClearCart = New Button()
        lblCartTotal = New Label()
        CType(dgvProducts, ComponentModel.ISupportInitialize).BeginInit()
        CType(nudQty, ComponentModel.ISupportInitialize).BeginInit()
        CType(dgvCart, ComponentModel.ISupportInitialize).BeginInit()
        SuspendLayout()
        '
        'lblServerUrl
        '
        lblServerUrl.AutoSize = True
        lblServerUrl.Location = New Point(26, 22)
        lblServerUrl.Name = "lblServerUrl"
        lblServerUrl.Size = New Size(64, 15)
        lblServerUrl.TabIndex = 0
        lblServerUrl.Text = "Server URL"
        '
        'txtServerUrl
        '
        txtServerUrl.Location = New Point(126, 19)
        txtServerUrl.Name = "txtServerUrl"
        txtServerUrl.Size = New Size(430, 23)
        txtServerUrl.TabIndex = 1
        '
        'lblEmail
        '
        lblEmail.AutoSize = True
        lblEmail.Location = New Point(26, 58)
        lblEmail.Name = "lblEmail"
        lblEmail.Size = New Size(36, 15)
        lblEmail.TabIndex = 2
        lblEmail.Text = "Email"
        '
        'txtEmail
        '
        txtEmail.Location = New Point(126, 55)
        txtEmail.Name = "txtEmail"
        txtEmail.Size = New Size(220, 23)
        txtEmail.TabIndex = 3
        '
        'lblPassword
        '
        lblPassword.AutoSize = True
        lblPassword.Location = New Point(362, 58)
        lblPassword.Name = "lblPassword"
        lblPassword.Size = New Size(57, 15)
        lblPassword.TabIndex = 4
        lblPassword.Text = "Password"
        '
        'txtPassword
        '
        txtPassword.Location = New Point(436, 55)
        txtPassword.Name = "txtPassword"
        txtPassword.PasswordChar = "*"c
        txtPassword.Size = New Size(183, 23)
        txtPassword.TabIndex = 5
        '
        'lblDeviceId
        '
        lblDeviceId.AutoSize = True
        lblDeviceId.Location = New Point(26, 94)
        lblDeviceId.Name = "lblDeviceId"
        lblDeviceId.Size = New Size(53, 15)
        lblDeviceId.TabIndex = 6
        lblDeviceId.Text = "Device Id"
        '
        'txtDeviceId
        '
        txtDeviceId.Location = New Point(126, 91)
        txtDeviceId.Name = "txtDeviceId"
        txtDeviceId.Size = New Size(220, 23)
        txtDeviceId.TabIndex = 7
        '
        'btnLogin
        '
        btnLogin.Location = New Point(362, 90)
        btnLogin.Name = "btnLogin"
        btnLogin.Size = New Size(101, 25)
        btnLogin.TabIndex = 8
        btnLogin.Text = "Login"
        btnLogin.UseVisualStyleBackColor = True
        '
        'btnLoadProducts
        '
        btnLoadProducts.Enabled = False
        btnLoadProducts.Location = New Point(478, 90)
        btnLoadProducts.Name = "btnLoadProducts"
        btnLoadProducts.Size = New Size(141, 25)
        btnLoadProducts.TabIndex = 9
        btnLoadProducts.Text = "Load Products"
        btnLoadProducts.UseVisualStyleBackColor = True
        '
        'btnLogout
        '
        btnLogout.Enabled = False
        btnLogout.Location = New Point(635, 90)
        btnLogout.Name = "btnLogout"
        btnLogout.Size = New Size(70, 25)
        btnLogout.TabIndex = 10
        btnLogout.Text = "Logout"
        btnLogout.UseVisualStyleBackColor = True
        '
        'lblStatus
        '
        lblStatus.AutoSize = True
        lblStatus.Location = New Point(26, 130)
        lblStatus.Name = "lblStatus"
        lblStatus.Size = New Size(74, 15)
        lblStatus.TabIndex = 10
        lblStatus.Text = "Not logged in"
        '
        'dgvProducts
        '
        dgvProducts.AllowUserToAddRows = False
        dgvProducts.AllowUserToDeleteRows = False
        dgvProducts.Anchor = AnchorStyles.Top Or AnchorStyles.Bottom Or AnchorStyles.Left Or AnchorStyles.Right
        dgvProducts.ColumnHeadersHeightSizeMode = DataGridViewColumnHeadersHeightSizeMode.AutoSize
        dgvProducts.Location = New Point(26, 192)
        dgvProducts.MultiSelect = False
        dgvProducts.Name = "dgvProducts"
        dgvProducts.ReadOnly = True
        dgvProducts.RowTemplate.Height = 25
        dgvProducts.SelectionMode = DataGridViewSelectionMode.FullRowSelect
        dgvProducts.Size = New Size(430, 334)
        dgvProducts.TabIndex = 11
        '
        'lblSearch
        '
        lblSearch.AutoSize = True
        lblSearch.Location = New Point(26, 166)
        lblSearch.Name = "lblSearch"
        lblSearch.Size = New Size(77, 15)
        lblSearch.TabIndex = 12
        lblSearch.Text = "Search product"
        '
        'txtSearch
        '
        txtSearch.Location = New Point(126, 163)
        txtSearch.Name = "txtSearch"
        txtSearch.Size = New Size(330, 23)
        txtSearch.TabIndex = 13
        '
        'lblQty
        '
        lblQty.AutoSize = True
        lblQty.Location = New Point(476, 197)
        lblQty.Name = "lblQty"
        lblQty.Size = New Size(53, 15)
        lblQty.TabIndex = 14
        lblQty.Text = "Quantity"
        '
        'nudQty
        '
        nudQty.DecimalPlaces = 2
        nudQty.Location = New Point(540, 194)
        nudQty.Maximum = New Decimal(New Integer() {1000000, 0, 0, 0})
        nudQty.Minimum = New Decimal(New Integer() {1, 0, 0, 0})
        nudQty.Name = "nudQty"
        nudQty.Size = New Size(95, 23)
        nudQty.TabIndex = 15
        nudQty.Value = New Decimal(New Integer() {1, 0, 0, 0})
        '
        'lblRate
        '
        lblRate.AutoSize = True
        lblRate.Location = New Point(649, 197)
        lblRate.Name = "lblRate"
        lblRate.Size = New Size(30, 15)
        lblRate.TabIndex = 16
        lblRate.Text = "Rate"
        '
        'txtRate
        '
        txtRate.Location = New Point(687, 194)
        txtRate.Name = "txtRate"
        txtRate.Size = New Size(86, 23)
        txtRate.TabIndex = 17
        txtRate.Text = "0"
        '
        'btnAddToCart
        '
        btnAddToCart.Enabled = False
        btnAddToCart.Location = New Point(476, 223)
        btnAddToCart.Name = "btnAddToCart"
        btnAddToCart.Size = New Size(297, 27)
        btnAddToCart.TabIndex = 18
        btnAddToCart.Text = "Add Selected Product To Cart"
        btnAddToCart.UseVisualStyleBackColor = True
        '
        'dgvCart
        '
        dgvCart.AllowUserToAddRows = False
        dgvCart.AllowUserToDeleteRows = False
        dgvCart.ColumnHeadersHeightSizeMode = DataGridViewColumnHeadersHeightSizeMode.AutoSize
        dgvCart.Location = New Point(476, 256)
        dgvCart.MultiSelect = False
        dgvCart.Name = "dgvCart"
        dgvCart.ReadOnly = True
        dgvCart.RowTemplate.Height = 25
        dgvCart.SelectionMode = DataGridViewSelectionMode.FullRowSelect
        dgvCart.Size = New Size(297, 240)
        dgvCart.TabIndex = 19
        '
        'btnRemoveCartItem
        '
        btnRemoveCartItem.Location = New Point(476, 502)
        btnRemoveCartItem.Name = "btnRemoveCartItem"
        btnRemoveCartItem.Size = New Size(110, 24)
        btnRemoveCartItem.TabIndex = 20
        btnRemoveCartItem.Text = "Remove Item"
        btnRemoveCartItem.UseVisualStyleBackColor = True
        '
        'btnClearCart
        '
        btnClearCart.Location = New Point(592, 502)
        btnClearCart.Name = "btnClearCart"
        btnClearCart.Size = New Size(80, 24)
        btnClearCart.TabIndex = 21
        btnClearCart.Text = "Clear Cart"
        btnClearCart.UseVisualStyleBackColor = True
        '
        'lblCartTotal
        '
        lblCartTotal.AutoSize = True
        lblCartTotal.Location = New Point(678, 507)
        lblCartTotal.Name = "lblCartTotal"
        lblCartTotal.Size = New Size(95, 15)
        lblCartTotal.TabIndex = 22
        lblCartTotal.Text = "Total Amount: 0"
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(800, 540)
        Me.Controls.Add(lblCartTotal)
        Me.Controls.Add(btnClearCart)
        Me.Controls.Add(btnRemoveCartItem)
        Me.Controls.Add(dgvCart)
        Me.Controls.Add(btnAddToCart)
        Me.Controls.Add(txtRate)
        Me.Controls.Add(lblRate)
        Me.Controls.Add(nudQty)
        Me.Controls.Add(lblQty)
        Me.Controls.Add(txtSearch)
        Me.Controls.Add(lblSearch)
        Me.Controls.Add(dgvProducts)
        Me.Controls.Add(lblStatus)
        Me.Controls.Add(btnLogout)
        Me.Controls.Add(btnLoadProducts)
        Me.Controls.Add(btnLogin)
        Me.Controls.Add(txtDeviceId)
        Me.Controls.Add(lblDeviceId)
        Me.Controls.Add(txtPassword)
        Me.Controls.Add(lblPassword)
        Me.Controls.Add(txtEmail)
        Me.Controls.Add(lblEmail)
        Me.Controls.Add(txtServerUrl)
        Me.Controls.Add(lblServerUrl)
        Me.Name = "Form1"
        Me.Text = "Malraja POS - Phase 1"
        CType(dgvProducts, ComponentModel.ISupportInitialize).EndInit()
        CType(nudQty, ComponentModel.ISupportInitialize).EndInit()
        CType(dgvCart, ComponentModel.ISupportInitialize).EndInit()
        ResumeLayout(False)
        PerformLayout()
    End Sub

End Class
