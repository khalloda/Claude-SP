<?php
return [
    'app' => [
        'name' => 'نظام قطع الغيار',
        'welcome' => 'مرحباً بك في نظام إدارة قطع الغيار',
        'description' => 'حل شامل ثنائي اللغة لإدارة قطع الغيار'
    ],
    
    'auth' => [
        'login' => 'تسجيل الدخول',
        'logout' => 'تسجيل الخروج',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'remember_me' => 'تذكرني',
        'forgot_password' => 'نسيت كلمة المرور؟',
        'login_failed' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
        'logout_success' => 'تم تسجيل الخروج بنجاح',
        'access_denied' => 'الوصول مرفوض. يرجى تسجيل الدخول.',
        'session_expired' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى.'
    ],
    
    'navigation' => [
        'dashboard' => 'لوحة التحكم',
        'masters' => 'البيانات الرئيسية',
        'clients' => 'العملاء',
        'suppliers' => 'الموردين',
        'warehouses' => 'المستودعات',
        'products' => 'المنتجات',
        'dropdowns' => 'القوائم المنسدلة',
        'sales' => 'المبيعات',
        'quotes' => 'عروض الأسعار',
        'sales_orders' => 'أوامر البيع',
        'invoices' => 'الفواتير',
        'payments' => 'المدفوعات',
        'reports' => 'التقارير',
        'settings' => 'الإعدادات'
    ],
    
    'dashboard' => [
        'title' => 'لوحة التحكم',
        'welcome' => 'مرحباً بعودتك!',
        'stats' => [
            'total_clients' => 'إجمالي العملاء',
            'total_products' => 'إجمالي المنتجات',
            'total_warehouses' => 'إجمالي المستودعات',
            'low_stock_items' => 'المنتجات منخفضة المخزون',
            'pending_quotes' => 'عروض الأسعار المعلقة',
            'open_orders' => 'الطلبات المفتوحة',
            'unpaid_invoices' => 'الفواتير غير المدفوعة',
            'monthly_sales' => 'المبيعات الشهرية'
        ],
        'recent_activity' => 'النشاط الأخير',
        'quick_actions' => 'إجراءات سريعة'
    ],
    
    'clients' => [
        'title' => 'العملاء',
        'create' => 'إنشاء عميل',
        'edit' => 'تعديل عميل',
        'view' => 'عرض عميل',
        'type' => 'نوع العميل',
        'company' => 'شركة',
        'individual' => 'فرد',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'address' => 'العنوان',
        'profile' => 'ملف العميل',
        'tabs' => [
            'details' => 'التفاصيل',
            'quotes' => 'عروض الأسعار',
            'orders' => 'أوامر البيع',
            'invoices' => 'الفواتير',
            'payments' => 'المدفوعات',
            'balance' => 'الرصيد'
        ]
    ],
    
    'suppliers' => [
        'title' => 'الموردين',
        'create' => 'إنشاء مورد',
        'edit' => 'تعديل مورد',
        'view' => 'عرض مورد',
        'type' => 'نوع المورد',
        'company' => 'شركة',
        'individual' => 'فرد',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'address' => 'العنوان'
    ],
    
    'warehouses' => [
        'title' => 'المستودعات',
        'create' => 'إنشاء مستودع',
        'edit' => 'تعديل مستودع',
        'view' => 'عرض مستودع',
        'name' => 'اسم المستودع',
        'capacity' => 'السعة',
        'responsible_person' => 'المسؤول',
        'phone' => 'الهاتف',
        'address' => 'العنوان',
        'inventory' => 'المخزون',
        'total_value' => 'القيمة الإجمالية'
    ],
    
    'products' => [
        'title' => 'المنتجات',
        'create' => 'إنشاء منتج',
        'edit' => 'تعديل منتج',
        'view' => 'عرض منتج',
        'code' => 'كود المنتج',
        'name' => 'اسم المنتج',
        'classification' => 'التصنيف',
        'color' => 'اللون',
        'brand' => 'الماركة',
        'car_make' => 'ماركة السيارة',
        'car_model' => 'موديل السيارة',
        'description' => 'الوصف',
        'total_qty' => 'الكمية الإجمالية',
        'reserved_quotes' => 'محجوز (عروض الأسعار)',
        'reserved_orders' => 'محجوز (الطلبات)',
        'available_qty' => 'الكمية المتاحة',
        'price' => 'السعر',
        'warehouse_locations' => 'مواقع المستودعات',
        'low_stock' => 'مخزون منخفض',
        'out_of_stock' => 'نفذ المخزون',
        'in_stock' => 'متوفر'
    ],
    
    'quotes' => [
        'title' => 'عروض الأسعار',
        'create' => 'إنشاء عرض سعر',
        'edit' => 'تعديل عرض سعر',
        'view' => 'عرض عرض السعر',
        'client' => 'العميل',
        'status' => 'الحالة',
        'items' => 'المنتجات',
        'subtotal' => 'المجموع الفرعي',
        'tax' => 'الضريبة',
        'discount' => 'الخصم',
        'total' => 'المجموع',
        'notes' => 'ملاحظات',
        'actions' => [
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'convert_to_order' => 'تحويل إلى طلب'
        ],
        'status_options' => [
            'draft' => 'مسودة',
            'sent' => 'مرسل',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض'
        ]
    ],
    
    'sales_orders' => [
        'title' => 'أوامر البيع',
        'create' => 'إنشاء أمر بيع',
        'edit' => 'تعديل أمر بيع',
        'view' => 'عرض أمر البيع',
        'client' => 'العميل',
        'quote' => 'عرض السعر المرتبط',
        'status' => 'الحالة',
        'items' => 'المنتجات',
        'subtotal' => 'المجموع الفرعي',
        'tax' => 'الضريبة',
        'discount' => 'الخصم',
        'total' => 'المجموع',
        'notes' => 'ملاحظات',
        'actions' => [
            'ship' => 'شحن',
            'deliver' => 'تأكيد التسليم',
            'reject' => 'رفض',
            'convert_to_invoice' => 'تحويل إلى فاتورة'
        ],
        'status_options' => [
            'open' => 'مفتوح',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغي'
        ]
    ],
    
    'invoices' => [
        'title' => 'الفواتير',
        'create' => 'إنشاء فاتورة',
        'edit' => 'تعديل فاتورة',
        'view' => 'عرض فاتورة',
        'client' => 'العميل',
        'sales_order' => 'أمر البيع المرتبط',
        'status' => 'الحالة',
        'items' => 'المنتجات',
        'subtotal' => 'المجموع الفرعي',
        'tax' => 'الضريبة',
        'discount' => 'الخصم',
        'total' => 'المجموع',
        'paid' => 'المبلغ المدفوع',
        'balance' => 'الرصيد',
        'notes' => 'ملاحظات',
        'actions' => [
            'add_payment' => 'إضافة دفعة',
            'void' => 'إلغاء الفاتورة'
        ],
        'status_options' => [
            'open' => 'مفتوحة',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            'void' => 'ملغية'
        ]
    ],
    
    'payments' => [
        'title' => 'المدفوعات',
        'create' => 'إنشاء دفعة',
        'edit' => 'تعديل دفعة',
        'view' => 'عرض دفعة',
        'client' => 'العميل',
        'invoice' => 'الفاتورة',
        'amount' => 'المبلغ',
        'method' => 'طريقة الدفع',
        'date' => 'تاريخ الدفع',
        'notes' => 'ملاحظات',
        'methods' => [
            'cash' => 'نقداً',
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'other' => 'أخرى'
        ]
    ],
    
    'common' => [
        'id' => 'الرقم التعريفي',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'address' => 'العنوان',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'action' => 'إجراء',
        'actions' => 'الإجراءات',
        'total' => 'المجموع',
        'subtotal' => 'المجموع الفرعي',
        'quantity' => 'الكمية',
        'price' => 'السعر',
        'search' => 'بحث',
        'filter' => 'تصفية',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'all' => 'الكل',
        'active' => 'نشط',
        'inactive' => 'غير نشط'
    ],
    
    'actions' => [
        'create' => 'إنشاء',
        'edit' => 'تعديل',
        'view' => 'عرض',
        'delete' => 'حذف',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'submit' => 'إرسال',
        'update' => 'تحديث',
        'search' => 'بحث',
        'filter' => 'تصفية',
        'export' => 'تصدير',
        'import' => 'استيراد',
        'back' => 'رجوع',
        'next' => 'التالي',
        'previous' => 'السابق',
        'confirm' => 'تأكيد',
        'approve' => 'موافقة',
        'reject' => 'رفض'
    ],
    
    'messages' => [
        'success' => 'تمت العملية بنجاح',
        'error' => 'حدث خطأ',
        'created' => 'تم إنشاء السجل بنجاح',
        'updated' => 'تم تحديث السجل بنجاح',
        'deleted' => 'تم حذف السجل بنجاح',
        'not_found' => 'السجل غير موجود',
        'validation_error' => 'يرجى مراجعة النموذج للأخطاء',
        'access_denied' => 'الوصول مرفوض',
        'session_expired' => 'انتهت صلاحية الجلسة',
        'confirm_delete' => 'هل أنت متأكد من رغبتك في حذف هذا السجل؟',
        'no_records' => 'لا توجد سجلات',
        'loading' => 'جاري التحميل...',
        'saving' => 'جاري الحفظ...'
    ],
    
    'validation' => [
        'required' => 'هذا الحقل مطلوب',
        'email' => 'يرجى إدخال عنوان بريد إلكتروني صحيح',
        'numeric' => 'يجب أن يكون هذا الحقل رقمياً',
        'min_length' => 'يجب أن يكون هذا الحقل على الأقل :min أحرف',
        'max_length' => 'يجب ألا يزيد هذا الحقل عن :max أحرف',
        'unique' => 'هذه القيمة موجودة بالفعل'
    ]
];
