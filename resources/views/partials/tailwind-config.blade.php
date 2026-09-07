{{-- Hanya font. Styling Tailwind kini dari build Vite (resources/css/app.css).
    Daftar ikon Material Symbols yang dipakai ada di param text= di bawah;
    tambah nama ikon baru ke sana bila memakai ikon baru. --}}
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
{{-- Font di-batasi weight + non-blocking (media=print swap) agar tidak block render mobile. --}}
<link rel="preload" as="style"
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@400;500;600;700&display=swap" />
<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet" media="print" onload="this.media='all'" />
{{-- Material Symbols di-subset ke ~40 glyph yang dipakai (lihat daftar di bawah).
    Tambah nama ikon baru ke param text= bila memakai ikon baru. --}}
<link rel="preload" as="style"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&text=airport_shuttle,arrow_back,arrow_forward,call,calendar_month,calendar_today,cancel,check_circle,error,event,expand_more,fitness_center,groups,hotel,hotel_class,local_offer,local_parking,location_on,mail,meeting_room,menu,open_in_new,payments,person,pool,print,receipt_long,restaurant,schedule,search,search_off,send,shield_check,spa,star,support_agent,verified,wifi&display=swap" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&text=airport_shuttle,arrow_back,arrow_forward,call,calendar_month,calendar_today,cancel,check_circle,error,event,expand_more,fitness_center,groups,hotel,hotel_class,local_offer,local_parking,location_on,mail,meeting_room,menu,open_in_new,payments,person,pool,print,receipt_long,restaurant,schedule,search,search_off,send,shield_check,spa,star,support_agent,verified,wifi&display=swap"
    rel="stylesheet" media="print" onload="this.media='all'" />
<noscript>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
</noscript>
