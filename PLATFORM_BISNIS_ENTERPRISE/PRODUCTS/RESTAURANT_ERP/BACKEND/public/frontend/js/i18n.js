// Internationalization (i18n) for Consumer App
const translations = {
    id: {
        app: {
            title: "EBP Restaurant"
        },
        guest: "Tamu",
        login_prompt: "Login untuk akses fitur",
        nav: {
            home: "Beranda",
            search: "Cari",
            reservations: "Reservasi",
            orders: "Pesanan",
            favorites: "Favorit",
            loyalty: "Loyalitas",
            settings: "Pengaturan",
            help: "Bantuan",
            profile: "Profil"
        },
        home: {
            discover: "Temukan Restoran",
            featured: "Restoran Unggulan",
            nearby: "Restoran Terdekat",
            cuisines: "Masakan Populer"
        },
        search: {
            title: "Cari",
            placeholder: "Cari restoran, masakan, hidangan..."
        },
        filter: {
            all: "Semua",
            nearby: "Terdekat",
            top_rated: "Terbaik",
            halal: "Halal",
            delivery: "Antar",
            cuisine: "Masakan",
            price: "Rentang Harga",
            rating: "Rating",
            features: "Fitur",
            apply: "Terapkan Filter"
        },
        price: {
            cheap: "$",
            moderate: "$$",
            expensive: "$$$",
            luxury: "$$$$"
        },
        rating: {
            "4_plus": "4+ Bintang",
            "3_plus": "3+ Bintang"
        },
        feature: {
            halal: "Halal",
            delivery: "Antar",
            pickup: "Ambil Sendiri",
            reservation: "Reservasi"
        },
        reservations: {
            title: "Reservasi Saya",
            new: "+ Baru"
        },
        orders: {
            title: "Pesanan Saya"
        },
        favorites: {
            title: "Favorit Saya"
        },
        loyalty: {
            title: "Program Loyalitas"
        },
        settings: {
            title: "Pengaturan",
            language: "Bahasa",
            notifications: "Notifikasi",
            dark_mode: "Mode Gelap",
            location: "Layanan Lokasi"
        },
        help: {
            title: "Bantuan & Dukungan",
            faq: "FAQ",
            contact: "Hubungi Kami",
            chat: "Chat Dukungan",
            email: "Email Dukungan"
        },
        reservation: {
            make_title: "Buat Reservasi",
            restaurant: "Restoran",
            date: "Tanggal",
            time: "Waktu",
            party_size: "Jumlah Orang",
            special_requests: "Permintaan Khusus",
            submit: "Pesan Meja"
        },
        order: {
            title: "Buat Pesanan",
            subtotal: "Subtotal",
            delivery_fee: "Biaya Antar",
            total: "Total",
            delivery: "Antar",
            pickup: "Ambil Sendiri",
            address: "Alamat Antar",
            place: "Buat Pesanan"
        },
        review: {
            title: "Tulis Ulasan",
            rating: "Rating",
            comment: "Ulasan Anda",
            photos: "Tambah Foto",
            submit: "Kirim Ulasan"
        },
        login: {
            title: "Login",
            email: "Email",
            password: "Password",
            submit: "Login",
            no_account: "Belum punya akun?",
            signup: "Daftar"
        }
    },
    en: {
        app: {
            title: "EBP Restaurant"
        },
        guest: "Guest",
        login_prompt: "Login to access features",
        nav: {
            home: "Home",
            search: "Search",
            reservations: "Reservations",
            orders: "Orders",
            favorites: "Favorites",
            loyalty: "Loyalty",
            settings: "Settings",
            help: "Help",
            profile: "Profile"
        },
        home: {
            discover: "Discover Restaurants",
            featured: "Featured Restaurants",
            nearby: "Nearby Restaurants",
            cuisines: "Popular Cuisines"
        },
        search: {
            title: "Search",
            placeholder: "Search restaurants, cuisines, dishes..."
        },
        filter: {
            all: "All",
            nearby: "Nearby",
            top_rated: "Top Rated",
            halal: "Halal",
            delivery: "Delivery",
            cuisine: "Cuisine",
            price: "Price Range",
            rating: "Rating",
            features: "Features",
            apply: "Apply Filters"
        },
        price: {
            cheap: "$",
            moderate: "$$",
            expensive: "$$$",
            luxury: "$$$$"
        },
        rating: {
            "4_plus": "4+ Stars",
            "3_plus": "3+ Stars"
        },
        feature: {
            halal: "Halal",
            delivery: "Delivery",
            pickup: "Pickup",
            reservation: "Reservation"
        },
        reservations: {
            title: "My Reservations",
            new: "+ New"
        },
        orders: {
            title: "My Orders"
        },
        favorites: {
            title: "My Favorites"
        },
        loyalty: {
            title: "Loyalty Program"
        },
        settings: {
            title: "Settings",
            language: "Language",
            notifications: "Notifications",
            dark_mode: "Dark Mode",
            location: "Location Services"
        },
        help: {
            title: "Help & Support",
            faq: "FAQ",
            contact: "Contact Us",
            chat: "Chat Support",
            email: "Email Support"
        },
        reservation: {
            make_title: "Make a Reservation",
            restaurant: "Restaurant",
            date: "Date",
            time: "Time",
            party_size: "Party Size",
            special_requests: "Special Requests",
            submit: "Book Table"
        },
        order: {
            title: "Place Order",
            subtotal: "Subtotal",
            delivery_fee: "Delivery Fee",
            total: "Total",
            delivery: "Delivery",
            pickup: "Pickup",
            address: "Delivery Address",
            place: "Place Order"
        },
        review: {
            title: "Write a Review",
            rating: "Rating",
            comment: "Your Review",
            photos: "Add Photos",
            submit: "Submit Review"
        },
        login: {
            title: "Login",
            email: "Email",
            password: "Password",
            submit: "Login",
            no_account: "Don't have an account?",
            signup: "Sign Up"
        }
    }
};

class I18n {
    constructor() {
        this.currentLang = localStorage.getItem('language') || 'id';
        this.translations = translations;
    }

    setLanguage(lang) {
        this.currentLang = lang;
        localStorage.setItem('language', lang);
        this.updatePage();
    }

    getLanguage() {
        return this.currentLang;
    }

    t(key) {
        const keys = key.split('.');
        let value = this.translations[this.currentLang];
        
        for (const k of keys) {
            if (value && value[k]) {
                value = value[k];
            } else {
                return key;
            }
        }
        
        return value;
    }

    updatePage() {
        // Update elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            el.textContent = this.t(key);
        });

        // Update elements with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = this.t(key);
        });

        // Update language button
        const langBtn = document.getElementById('langBtn');
        if (langBtn) {
            langBtn.textContent = this.currentLang.toUpperCase();
        }

        // Update language select
        const langSelect = document.getElementById('languageSelect');
        if (langSelect) {
            langSelect.value = this.currentLang;
        }
    }
}

const i18n = new I18n();
