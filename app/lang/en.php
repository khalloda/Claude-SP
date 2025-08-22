<?php
return [
    'app' => [
        'name' => 'Spare Parts System',
        'welcome' => 'Welcome to Spare Parts Management System',
        'description' => 'Comprehensive bilingual spare parts management solution'
    ],
    
    'auth' => [
        'login' => 'Login',
        'logout' => 'Logout',
        'email' => 'Email Address',
        'password' => 'Password',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot Password?',
        'login_failed' => 'Invalid email or password',
        'logout_success' => 'You have been logged out successfully',
        'access_denied' => 'Access denied. Please login.',
        'session_expired' => 'Your session has expired. Please login again.'
    ],
    
    'navigation' => [
        'dashboard' => 'Dashboard',
        'masters' => 'Masters',
        'clients' => 'Clients',
        'suppliers' => 'Suppliers',
        'warehouses' => 'Warehouses',
        'products' => 'Products',
        'dropdowns' => 'Dropdowns',
        'sales' => 'Sales',
        'quotes' => 'Quotes',
        'sales_orders' => 'Sales Orders',
        'invoices' => 'Invoices',
        'payments' => 'Payments',
        'reports' => 'Reports',
        'settings' => 'Settings'
    ],
    
    'dashboard' => [
        'title' => 'Dashboard',
        'welcome' => 'Welcome back!',
        'stats' => [
            'total_clients' => 'Total Clients',
            'total_products' => 'Total Products',
            'total_warehouses' => 'Total Warehouses',
            'low_stock_items' => 'Low Stock Items',
            'pending_quotes' => 'Pending Quotes',
            'open_orders' => 'Open Orders',
            'unpaid_invoices' => 'Unpaid Invoices',
            'monthly_sales' => 'Monthly Sales'
        ],
        'recent_activity' => 'Recent Activity',
        'quick_actions' => 'Quick Actions'
    ],
    
    'clients' => [
        'title' => 'Clients',
        'create' => 'Create Client',
        'edit' => 'Edit Client',
        'view' => 'View Client',
        'type' => 'Client Type',
        'company' => 'Company',
        'individual' => 'Individual',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'address' => 'Address',
        'profile' => 'Client Profile',
        'tabs' => [
            'details' => 'Details',
            'quotes' => 'Quotes',
            'orders' => 'Sales Orders',
            'invoices' => 'Invoices',
            'payments' => 'Payments',
            'balance' => 'Balance'
        ]
    ],
    
    'suppliers' => [
        'title' => 'Suppliers',
        'create' => 'Create Supplier',
        'edit' => 'Edit Supplier',
        'view' => 'View Supplier',
        'type' => 'Supplier Type',
        'company' => 'Company',
        'individual' => 'Individual',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'address' => 'Address'
    ],
    
    'warehouses' => [
        'title' => 'Warehouses',
        'create' => 'Create Warehouse',
        'edit' => 'Edit Warehouse',
        'view' => 'View Warehouse',
        'name' => 'Warehouse Name',
        'capacity' => 'Capacity',
        'responsible_person' => 'Responsible Person',
        'phone' => 'Phone',
        'address' => 'Address',
        'inventory' => 'Inventory',
        'total_value' => 'Total Value'
    ],
    
    'products' => [
        'title' => 'Products',
        'create' => 'Create Product',
        'edit' => 'Edit Product',
        'view' => 'View Product',
        'code' => 'Product Code',
        'name' => 'Product Name',
        'classification' => 'Classification',
        'color' => 'Color',
        'brand' => 'Brand',
        'car_make' => 'Car Make',
        'car_model' => 'Car Model',
        'description' => 'Description',
        'total_qty' => 'Total Quantity',
        'reserved_quotes' => 'Reserved (Quotes)',
        'reserved_orders' => 'Reserved (Orders)',
        'available_qty' => 'Available Quantity',
        'price' => 'Price',
        'warehouse_locations' => 'Warehouse Locations',
        'low_stock' => 'Low Stock',
        'out_of_stock' => 'Out of Stock',
        'in_stock' => 'In Stock'
    ],
    
    'quotes' => [
        'title' => 'Quotes',
        'create' => 'Create Quote',
        'edit' => 'Edit Quote',
        'view' => 'View Quote',
        'client' => 'Client',
        'status' => 'Status',
        'items' => 'Items',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'discount' => 'Discount',
        'total' => 'Total',
        'notes' => 'Notes',
        'actions' => [
            'approve' => 'Approve',
            'reject' => 'Reject',
            'convert_to_order' => 'Convert to Order'
        ],
        'status_options' => [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ]
    ],
    
    'sales_orders' => [
        'title' => 'Sales Orders',
        'create' => 'Create Sales Order',
        'edit' => 'Edit Sales Order',
        'view' => 'View Sales Order',
        'client' => 'Client',
        'quote' => 'Related Quote',
        'status' => 'Status',
        'items' => 'Items',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'discount' => 'Discount',
        'total' => 'Total',
        'notes' => 'Notes',
        'actions' => [
            'ship' => 'Ship',
            'deliver' => 'Mark as Delivered',
            'reject' => 'Reject',
            'convert_to_invoice' => 'Convert to Invoice'
        ],
        'status_options' => [
            'open' => 'Open',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled'
        ]
    ],
    
    'invoices' => [
        'title' => 'Invoices',
        'create' => 'Create Invoice',
        'edit' => 'Edit Invoice',
        'view' => 'View Invoice',
        'client' => 'Client',
        'sales_order' => 'Related Sales Order',
        'status' => 'Status',
        'items' => 'Items',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'discount' => 'Discount',
        'total' => 'Total',
        'paid' => 'Paid Amount',
        'balance' => 'Balance',
        'notes' => 'Notes',
        'actions' => [
            'add_payment' => 'Add Payment',
            'void' => 'Void Invoice'
        ],
        'status_options' => [
            'open' => 'Open',
            'partial' => 'Partially Paid',
            'paid' => 'Paid',
            'void' => 'Void'
        ]
    ],
    
    'payments' => [
        'title' => 'Payments',
        'create' => 'Create Payment',
        'edit' => 'Edit Payment',
        'view' => 'View Payment',
        'client' => 'Client',
        'invoice' => 'Invoice',
        'amount' => 'Amount',
        'method' => 'Payment Method',
        'date' => 'Payment Date',
        'notes' => 'Notes',
        'methods' => [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'other' => 'Other'
        ]
    ],
    
    'common' => [
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'address' => 'Address',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'action' => 'Action',
        'actions' => 'Actions',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'quantity' => 'Quantity',
        'price' => 'Price',
        'search' => 'Search',
        'filter' => 'Filter',
        'export' => 'Export',
        'import' => 'Import',
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive'
    ],
    
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'delete' => 'Delete',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'submit' => 'Submit',
        'update' => 'Update',
        'search' => 'Search',
        'filter' => 'Filter',
        'export' => 'Export',
        'import' => 'Import',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
        'confirm' => 'Confirm',
        'approve' => 'Approve',
        'reject' => 'Reject'
    ],
    
    'messages' => [
        'success' => 'Operation completed successfully',
        'error' => 'An error occurred',
        'created' => 'Record created successfully',
        'updated' => 'Record updated successfully',
        'deleted' => 'Record deleted successfully',
        'not_found' => 'Record not found',
        'validation_error' => 'Please check the form for errors',
        'access_denied' => 'Access denied',
        'session_expired' => 'Session expired',
        'confirm_delete' => 'Are you sure you want to delete this record?',
        'no_records' => 'No records found',
        'loading' => 'Loading...',
        'saving' => 'Saving...'
    ],
    
    'validation' => [
        'required' => 'This field is required',
        'email' => 'Please enter a valid email address',
        'numeric' => 'This field must be numeric',
        'min_length' => 'This field must be at least :min characters',
        'max_length' => 'This field must not exceed :max characters',
        'unique' => 'This value already exists'
    ]
];
