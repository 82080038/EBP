"""
Streamlit Page: System Compatibility Checker.
"""
import streamlit as st
import plotly.graph_objects as go


def render_system_check():
    st.markdown("# 💻 Cek Kecocokan Sistem")
    st.markdown("*Periksa apakah komputer Anda cocok untuk menjalankan aplikasi ini*")
    st.markdown("---")

    if st.button("🔄 Jalankan Cek Sistem", type="primary"):
        with st.spinner("Menganalisis hardware komputer..."):
            st.session_state["system_report"] = None  # Force re-check
            from src.system_check import check_system
            report = check_system()
            st.session_state["system_report"] = report

    if "system_report" not in st.session_state or st.session_state["system_report"] is None:
        # Auto-run on first load
        from src.system_check import check_system
        report = check_system()
        st.session_state["system_report"] = report

    report = st.session_state["system_report"]

    # === Big compatibility gauge ===
    col1, col2, col3 = st.columns([1, 2, 1])

    with col2:
        pct = report.compatibility_pct
        color = "#10b981" if pct >= 85 else "#f59e0b" if pct >= 70 else "#ef4444" if pct >= 50 else "#dc2626"

        fig = go.Figure(go.Indicator(
            mode="gauge+number",
            value=pct,
            domain={"x": [0, 1], "y": [0, 1]},
            title={"text": "Kecocokan Sistem", "font": {"size": 24}},
            number={"font": {"size": 48, "color": color}},
            gauge={
                "axis": {"range": [0, 100], "tickwidth": 1, "tickcolor": "white"},
                "bar": {"color": color},
                "bgcolor": "rgba(0,0,0,0)",
                "borderwidth": 2,
                "bordercolor": "#374151",
                "steps": [
                    {"range": [0, 50], "color": "#1f2937"},
                    {"range": [50, 70], "color": "#374151"},
                    {"range": [70, 85], "color": "#4b5563"},
                    {"range": [85, 100], "color": "#1f2937"},
                ],
                "threshold": {
                    "line": {"color": color, "width": 4},
                    "thickness": 0.75,
                    "value": pct,
                },
            },
        ))
        fig.update_layout(height=300, margin=dict(t=50, b=10, l=20, r=20))
        st.plotly_chart(fig, use_container_width=True)

        st.markdown(f"<h3 style='text-align: center; color: {color};'>{report.verdict}</h3>", unsafe_allow_html=True)

    st.markdown("---")

    # === Hardware specs ===
    st.markdown("### 🔧 Spesifikasi Komputer")

    col1, col2, col3, col4 = st.columns(4)
    with col1:
        st.metric("OS", f"{report.os_name}")
        st.metric("Python", report.python_version)
    with col2:
        st.metric("CPU", f"{report.cpu_cores}C / {report.cpu_threads}T")
        st.metric("CPU Freq", f"{report.cpu_freq_ghz} GHz")
    with col3:
        st.metric("Total RAM", f"{report.total_ram_gb} GB")
        st.metric("Free RAM", f"{report.available_ram_gb} GB")
    with col4:
        st.metric("Free Disk", f"{report.disk_free_gb} GB")
        st.metric("GPU", report.gpu_name if report.has_gpu else "None")

    with st.expander("📋 Detail Hardware"):
        st.markdown(f"**CPU Name:** {report.cpu_name}")
        st.markdown(f"**CPU Cores (physical):** {report.cpu_cores}")
        st.markdown(f"**CPU Threads (logical):** {report.cpu_threads}")
        st.markdown(f"**CPU Max Frequency:** {report.cpu_freq_ghz} GHz")
        st.markdown(f"**Total RAM:** {report.total_ram_gb} GB")
        st.markdown(f"**Available RAM:** {report.available_ram_gb} GB")
        st.markdown(f"**Disk Total:** {report.disk_total_gb} GB")
        st.markdown(f"**Disk Free:** {report.disk_free_gb} GB")
        st.markdown(f"**GPU:** {report.gpu_name if report.has_gpu else 'Not detected'}")
        if report.has_gpu:
            st.markdown(f"**GPU VRAM:** {report.gpu_vram_gb:.1f} GB")
        st.markdown(f"**OS Version:** {report.os_version}")

    st.markdown("---")

    # === Detailed checks ===
    st.markdown("### ✅ Pemeriksaan Detail")

    categories = {}
    for c in report.checks:
        if c.category not in categories:
            categories[c.category] = []
        categories[c.category].append(c)

    category_labels = {
        "cpu": "🖥️ CPU (Processor)",
        "ram": "💾 RAM (Memory)",
        "disk": "💿 Disk Storage",
        "gpu": "🎮 GPU (Graphics Card)",
        "python": "🐍 Python Version",
        "package": "📦 Installed Packages",
    }

    for cat, label in category_labels.items():
        if cat not in categories:
            continue

        checks = categories[cat]
        all_passed = all(c.passed for c in checks)
        avg_score = sum(c.score for c in checks) / len(checks) if checks else 0

        status_icon = "✅" if all_passed else "⚠️" if avg_score >= 0.5 else "❌"

        with st.expander(f"{status_icon} {label} — Score: {avg_score:.0%}", expanded=all_passed):
            for c in checks:
                col1, col2, col3 = st.columns([3, 2, 1])
                with col1:
                    icon = "✅" if c.passed else "⚠️" if c.score >= 0.5 else "❌"
                    st.markdown(f"**{icon} {c.name}**")
                    st.caption(c.notes)
                with col2:
                    st.markdown(f"Current: `{c.current_value}`")
                    st.markdown(f"Required: `{c.required_value}`")
                    if c.recommended_value and c.recommended_value != c.required_value:
                        st.markdown(f"Recommended: `{c.recommended_value}`")
                with col3:
                    score_color = "#10b981" if c.score >= 0.8 else "#f59e0b" if c.score >= 0.5 else "#ef4444"
                    st.markdown(f"<h3 style='text-align:center; color:{score_color};'>{c.score:.0%}</h3>", unsafe_allow_html=True)

    st.markdown("---")

    # === Recommendations ===
    st.markdown("### 💡 Rekomendasi")

    for rec in report.recommendations:
        st.markdown(f"- {rec}")

    st.markdown("---")

    # === Application requirements info ===
    with st.expander("📖 Syarat Minimum & Rekomendasi Aplikasi"):
        st.markdown("""
        | Komponen | Minimum | Rekomendasi |
        |----------|---------|-------------|
        | CPU Cores | 4 cores | 8+ cores |
        | CPU Frequency | 2.0 GHz | 3.0+ GHz |
        | RAM | 8 GB | 16+ GB |
        | Disk Free | 5 GB | 20+ GB |
        | GPU | Optional (CPU works) | NVIDIA 8+ GB VRAM |
        | Python | 3.10+ | 3.12+ |

        **Catatan:**
        - Aplikasi ini menggunakan 201 fitur ML yang membutuhkan RAM signifikan
        - Training ensemble (RF + XGBoost + LightGBM) membutuhkan CPU multi-core
        - GPU opsional untuk model transformer (PatchTST, TFT)
        - Disk space untuk database SQLite, model artifacts, dan MLflow logs
        - Simulasi 56 hari dengan retrain harian membutuhkan ~4 menit di CPU modern
        """)
