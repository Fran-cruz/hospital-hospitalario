<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

const rootEl = ref(null)
const showPopover = ref(false)
const showToast = ref(false)
const toastMessage = ref('Copiado')
const isCopying = ref(false)
const mobileMenuOpen = ref(false)

const navItems = [
    { label: 'Inicio', href: '#hero' },
    { label: 'Servicios', href: '#services' },
    { label: 'Especialidades', href: '#specialties' },
    { label: 'Nosotros', href: '#features' },
    { label: 'Contacto', href: '#contact' },
]

const stats = [
    { value: '24/7', label: 'Atención de emergencias' },
    { value: '+12', label: 'Especialidades médicas' },
    { value: '+500', label: 'Pacientes atendidos' },
    { value: '15 años', label: 'Cuidando familias' },
]

const services = [
    {
        icon: '🩺',
        title: 'Consulta General',
        desc: 'Atención médica integral para evaluación, diagnóstico y seguimiento de tu salud.',
    },
    {
        icon: '👩‍⚕️',
        title: 'Especialistas',
        desc: 'Médicos capacitados en distintas áreas para brindarte una atención profesional y cercana.',
    },
    {
        icon: '📅',
        title: 'Citas Médicas',
        desc: 'Solicita tu cita de forma rápida y encuentra horarios disponibles según tu necesidad.',
    },
    {
        icon: '💊',
        title: 'Control y Seguimiento',
        desc: 'Acompañamiento constante para tratamientos, revisiones y bienestar del paciente.',
    },
]

const features = [
    {
        title: 'Atención humana y cercana',
        desc: 'Nos enfocamos en escuchar, acompañar y brindar confianza a cada paciente y su familia.',
    },
    {
        title: 'Equipo médico confiable',
        desc: 'Profesionales comprometidos con una atención segura, responsable y de calidad.',
    },
    {
        title: 'Instalaciones cómodas',
        desc: 'Espacios limpios, organizados y diseñados para una experiencia tranquila y profesional.',
    },
    {
        title: 'Compromiso con tu bienestar',
        desc: 'Nuestra prioridad es tu salud, con atención oportuna y orientación clara en cada visita.',
    },
]

const specialties = [
    'Medicina General',
    'Pediatría',
    'Ginecología',
    'Traumatología',
    'Odontología',
    'Dermatología',
    'Cardiología',
    'Nutrición',
    'Y mas...',
]

const testimonialCards = [
    {
        name: 'Andrea R.',
        role: 'Paciente',
        text: 'Me atendieron con mucha amabilidad y profesionalismo. Me sentí escuchada desde el primer momento.',
    },
    {
        name: 'Carlos M.',
        role: 'Familiar de paciente',
        text: 'La atención fue ordenada, rápida y muy humana. Se nota el compromiso de todo el personal.',
    },
    {
        name: 'María G.',
        role: 'Paciente',
        text: 'Encontré en Medi+Plus un lugar confiable para mis controles médicos y el cuidado de mi familia.',
    },
]

const footerLinks = {
    especialidades: ['Pediatría', 'Ginecología', 'Traumatología', 'Odontología'],
    sistema: [
        { label: 'Iniciar Sesión', href: '/login' },
        { label: 'Registrarse', href: '/register' },
        { label: 'Solicitar Cita', href: '/register' },
        { label: 'Portal del Paciente', href: '/login' },
    ],
    contacto: [
        { label: '📍 La Ceiba, Honduras', href: '#' },
        { label: '📞 +504 9459-9288', href: '#' },
        { label: '✉️ contacto@mediplus.hn', href: '#' },
        { label: '⏰ Lun–Vie 8:00am–5:00pm', href: '#' },
    ],
}

const year = computed(() => new Date().getFullYear())

let outsideClickHandler = null
let escapeHandler = null

const scrollToId = (href) => {
    if (!href.startsWith('#')) return
    const target = document.querySelector(href)
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' })
        mobileMenuOpen.value = false
    }
}

const togglePopover = () => {
    showPopover.value = !showPopover.value
}

const closePopover = () => {
    showPopover.value = false
}

const openToast = async (message) => {
    toastMessage.value = message
    showToast.value = true
    await nextTick()
    window.clearTimeout(openToast._timer)
    openToast._timer = window.setTimeout(() => {
        showToast.value = false
    }, 1800)
}

onMounted(() => {
    outsideClickHandler = (event) => {
        const popover = document.getElementById('more-popover')
        const button = document.getElementById('more-btn')
        if (!popover || !button) return

        const clickedInsidePopover = popover.contains(event.target)
        const clickedButton = button.contains(event.target)

        if (!clickedInsidePopover && !clickedButton) {
            showPopover.value = false
        }
    }

    escapeHandler = (event) => {
        if (event.key === 'Escape') {
            showPopover.value = false
            mobileMenuOpen.value = false
        }
    }

    document.addEventListener('click', outsideClickHandler)
    document.addEventListener('keydown', escapeHandler)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', outsideClickHandler)
    document.removeEventListener('keydown', escapeHandler)
    window.clearTimeout(openToast._timer)
})
</script>

<template>
    <div ref="rootEl" class="visualize-widget">
        <div id="vis-container">
            <nav>
                <div class="nav-logo">
                    <div class="nav-logo">
                        <img src="/logo.png"
                             alt="Clínica Medi+"
                             class="h-16 w-auto"
                             style="background: transparent !important;"
                        >
                    </div>
                </div>

                <button class="mobile-toggle" @click.stop="mobileMenuOpen = !mobileMenuOpen" aria-label="Abrir menú">
                    ☰
                </button>

                <ul class="nav-links" :class="{ open: mobileMenuOpen }">
                    <li v-for="item in navItems" :key="item.href">
                        <a :href="item.href" @click.prevent="scrollToId(item.href)">{{ item.label }}</a>
                    </li>
                </ul>

                <div class="nav-actions">
                    <a href="/login" class="btn btn-ghost pa-3">Iniciar sesión</a>
                    <a href="/register" class="btn btn-primary pa-3">Solicitar cita</a>
                </div>
            </nav>

            <main>
                <section id="hero" class="hero" style="background: linear-gradient(to top left, #67e8f9, #F0F6FF);">
                    <div class="hero-grid" >
                        <div class="hero-copy">
                            <span class="eyebrow">Bienvenidos a Medi+Plus</span>
                            <h1>Cuidamos de tu salud con atención profesional, cercana y confiable.</h1>
                            <p>
                                En Medi+Plus creemos que cada paciente merece una atención humana, clara y segura.
                                Nuestro compromiso es acompañarte con servicios médicos de calidad para ti y tu familia.
                            </p>

                            <div class="hero-cta">
                                <a href="/register" class="btn btn-primary btn-lg">Agendar cita</a>
                                <a href="#contact" class="btn btn-secondary btn-lg" @click.prevent="scrollToId('#contact')">Contáctanos</a>
                            </div>

                            <div class="hero-stats">
                                <div v-for="item in stats" :key="item.label" class="stat-card">
                                    <strong>{{ item.value }}</strong>
                                    <span>{{ item.label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="hero-card">
                            <div class="hero-card-top">
                                <span class="chip chip-blue">Atención Médica</span>
                                <span class="chip chip-green">Cuidado Integral</span>
                            </div>

                            <div class="dashboard-mock clinic-mock">
                                <div class="mock-content clinic-content full">
                                    <div class="mock-header">
                                        <div>
                                            <h3>Tu salud es nuestra prioridad</h3>
                                            <p>Servicios médicos confiables para toda la familia</p>
                                        </div>
                                        <span class="pill">Medi+Plus</span>
                                    </div>

                                    <div class="mock-metrics">
                                        <div class="metric">
                                            <span>Consulta general</span>
                                            <strong>Disponible</strong>
                                        </div>
                                        <div class="metric">
                                            <span>Especialistas</span>
                                            <strong>Atención</strong>
                                        </div>
                                        <div class="metric">
                                            <span>Citas</span>
                                            <strong>Rápidas</strong>
                                        </div>
                                    </div>

                                    <div class="mock-list">
                                        <div class="mock-row">
                                            <span>Atención personalizada</span>
                                            <span class="status status-success">Activa</span>
                                        </div>
                                        <div class="mock-row">
                                            <span>Equipo médico calificado</span>
                                            <span class="status status-info">Confiable</span>
                                        </div>
                                        <div class="mock-row">
                                            <span>Ambiente seguro y humano</span>
                                            <span class="status status-warning">Cálido</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <v-divider></v-divider>

                <section id="services" class="section" style="background: linear-gradient(to bottom left, #67e8f9, #F0F6FF);">
                    <div class="section-head center">
                        <span class="eyebrow">Nuestros servicios</span>
                        <h2>Atención médica pensada para tu bienestar</h2>
                        <p>Brindamos servicios clínicos con enfoque humano, profesional y accesible.</p>
                    </div>

                    <div class="cards-grid services-grid">
                        <article v-for="item in services" :key="item.title" class="service-card">
                            <div class="service-icon">{{ item.icon }}</div>
                            <h3>{{ item.title }}</h3>
                            <p>{{ item.desc }}</p>
                        </article>
                    </div>
                </section>
                <v-divider></v-divider>

                <section id="specialties" class="section section-soft"  style="background: linear-gradient(to top left, #67e8f9, #F0F6FF);">
                    <div class="section-head center">
                        <span class="eyebrow">Especialidades</span>
                        <h2>Áreas médicas para atenderte mejor</h2>
                        <p>Contamos con distintas especialidades para brindarte una atención integral según tus necesidades.</p>
                    </div>

                    <div class="specialties-grid">
                        <div v-for="item in specialties" :key="item" class="specialty-chip">
                            {{ item }}
                        </div>
                    </div>
                </section>
                <v-divider></v-divider>

                <section id="features" class="section" style="background: linear-gradient(to bottom left, #67e8f9, #F0F6FF);">
                    <div class="two-col">
                        <div class="vertical pa-16">
                            <span class="eyebrow text-blue-600  tracking-widest" style="font-size: 1.6rem; margin-top: 30px;">¿POR QUÉ ELEGIRNOS?</span>
                            <h2 style="font-size: 1.5rem;">Más que una clínica,</h2>
                            <h2 style="font-size: 1.3rem; text-indent: 40px; margin-top: 5px">un lugar de confianza para tu familia. . . </h2>
                            <p class="lead" style="margin-top: 20px">
                                En Medi+Plus buscamos que cada visita sea una experiencia de tranquilidad, cuidado y confianza,
                                respaldada por atención médica responsable y humana.
                            </p>
                        </div>

                        <div class="feature-list">
                            <article v-for="item in features" :key="item.title" class="feature-item">
                                <div class="feature-bullet">✓</div>
                                <div>
                                    <h3>{{ item.title }}</h3>
                                    <p>{{ item.desc }}</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>
                <v-divider></v-divider>

                <section class="section section-testimonials"  style="background: linear-gradient(to top left, #67e8f9, #F0F6FF);">
                    <div class="section-head center">
                        <span class="eyebrow">Testimonios</span>
                        <h2>Lo que dicen nuestros pacientes</h2>
                    </div>

                    <div class="cards-grid testimonials-grid">
                        <article v-for="item in testimonialCards" :key="item.name" class="testimonial-card">
                            <p>“{{ item.text }}”</p>
                            <div class="testimonial-meta">
                                <strong>{{ item.name }}</strong>
                                <span>{{ item.role }}</span>
                            </div>
                        </article>
                    </div>
                </section>
                <v-divider></v-divider>

                <section id="contact" class="section cta-section" style="background: linear-gradient(to bottom left, #67e8f9, #F0F6FF);">
                    <div class="cta-box">
                        <div>
                            <span class="eyebrow">Contáctanos</span>
                            <h2>Estamos listos para atenderte.</h2>
                            <p>
                                Agenda tu cita, consulta nuestras especialidades o comunícate con nosotros para recibir orientación.
                            </p>
                        </div>

                        <div class="cta-actions">
                            <a href="/register" class="btn btn-primary btn-lg">Solicitar cita</a>
                            <a href="/login" class="btn btn-secondary btn-lg">Portal paciente</a>
                        </div>
                    </div>
                </section>
            </main>

            <footer>
                <div class="footer-grid">
                    <div class="footer-col footer-brand">
                        <div class="nav-logo">
                            <img src="/logo.png"
                                 alt="Clínica Medi+"
                                 class="h-20 w-xl-auto"
                                 style="background: transparent !important;"
                            >
                        </div>
                        <p>
                            En Medi+Plus trabajamos para brindarte atención médica confiable, cercana y enfocada en tu bienestar.
                        </p>
                    </div>

                    <div class="footer-col">
                        <h4>Especialidades</h4>
                        <ul>
                            <li v-for="item in footerLinks.especialidades" :key="item">
                                <a href="#specialties" @click.prevent="scrollToId('#specialties')">{{ item }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4>Accesos</h4>
                        <ul>
                            <li v-for="item in footerLinks.sistema" :key="item.label">
                                <a :href="item.href">{{ item.label }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4>Contacto</h4>
                        <ul>
                            <li v-for="item in footerLinks.contacto" :key="item.label">
                                <a :href="item.href">{{ item.label }}</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="footer-bottom">
                    <span>© {{ year }} Medi+Plus. Todos los derechos reservados.</span>
                    <span>Cuidando tu salud con compromiso y confianza</span>
                </div>
            </footer>
        </div>

        <transition name="toast">
            <div v-if="showToast" id="copy-toast">{{ toastMessage }}</div>
        </transition>


    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap');

:root {
    color-scheme: light;
    --blue: #1a6fa3;
    --blue-d: #0d5180;
    --blue-l: #e8f4fb;
    --green: #3aaa6a;
    --green-d: #2a8a52;
    --green-l: #e8f7ef;
    --teal: #0ea5a0;
    --gray: #64748b;
    --gray-l: #f1f5f9;
    --white: #ffffff;
    --dark: #0f2236;
    --shadow: 0 4px 24px rgba(26, 111, 163, 0.1);
    --shadow-lg: 0 12px 48px rgba(26, 111, 163, 0.16);
    --radius: 24px;
}

* {
    box-sizing: border-box;
}

.visualize-widget {
    position: relative;
    width: 100%;
    background: var(--white);
    color: var(--dark);
    font-family: 'Nunito', sans-serif;
    overflow-x: hidden;
}

#vis-container {
    position: relative;
    min-height: 100vh;
    background:
        radial-gradient(circle at top right, rgba(58, 170, 106, 0.08), transparent 28%),
        radial-gradient(circle at left top, rgba(26, 111, 163, 0.1), transparent 34%),
        linear-gradient(180deg, #ffffff, #f8fbfd 60%, #ffffff);
}

nav {
    position: sticky;
    top: 0;
    z-index: 100;
    height: 72px;
    padding: 0 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(26, 111, 163, 0.08);
    box-shadow: 0 2px 12px rgba(26, 111, 163, 0.06);
}

.nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--blue), var(--green));
    color: white;
    font-size: 22px;
    font-weight: 900;
    box-shadow: var(--shadow);
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1;
}

.brand-main {
    font-weight: 900;
    font-size: 1.05rem;
    color: var(--dark);
}

.brand-sub {
    margin-top: 4px;
    font-size: 0.78rem;
    color: var(--gray);
    font-weight: 700;
}

.nav-links {
    display: flex;
    list-style: none;
    gap: 24px;
    padding: 0;
    margin: 0;
}

.nav-links a {
    color: var(--gray);
    text-decoration: none;
    font-weight: 800;
    font-size: 0.95rem;
    transition: color 0.2s ease;
}

.nav-links a:hover {
    color: var(--blue);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.mobile-toggle {
    display: none;
    border: none;
    background: transparent;
    font-size: 1.3rem;
    color: var(--dark);
    cursor: pointer;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    border-radius: 14px;
    font-weight: 900;
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        background 0.18s ease,
        border-color 0.18s ease,
        color 0.18s ease;
    cursor: pointer;
    border: none;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-lg {
    min-height: 50px;
    padding: 0 22px;
    font-size: 0.98rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--blue), var(--green));
    color: white;
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    box-shadow: var(--shadow-lg);
}

.btn-secondary {
    background: white;
    color: var(--blue-d);
    border: 1px solid rgba(26, 111, 163, 0.18);
}

.btn-secondary:hover {
    background: #f8fbff;
}

.btn-ghost {
    padding: 10px 14px;
    color: var(--dark);
    background: transparent;
}

.hero {
    padding: 40px 5% 40px;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 38px;
    align-items: center;
    min-height: calc(100vh - 120px);
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: var(--blue-l);
    color: var(--blue-d);
    font-weight: 900;
    font-size: 0.8rem;
    margin-bottom: 18px;
}

.hero-copy h1,
.section-head h2,
.two-col h2,
.cta-box h2 {
    font-family: 'Playfair Display', serif;
    color: var(--dark);
}

.hero-copy h1 {
    font-size: clamp(2.6rem, 5vw, 4.5rem);
    line-height: 1.03;
    margin: 0 0 18px;
    max-width: 12ch;
}

.hero-copy p {
    font-size: 1.08rem;
    line-height: 1.75;
    color: var(--gray);
    max-width: 60ch;
}

.hero-cta {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 28px;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-top: 30px;
}

.stat-card {
    background: white;
    border: 1px solid rgba(26, 111, 163, 0.08);
    border-radius: 18px;
    padding: 18px 16px;
    box-shadow: var(--shadow);
}

.stat-card strong {
    display: block;
    font-size: 1.35rem;
    color: var(--blue-d);
    font-weight: 900;
}

.stat-card span {
    display: block;
    margin-top: 6px;
    font-size: 0.88rem;
    color: var(--gray);
    font-weight: 700;
}

.hero-card {
    background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(232,244,251,0.7));
    border: 1px solid rgba(26, 111, 163, 0.08);
    border-radius: 28px;
    padding: 22px;
    box-shadow: var(--shadow-lg);
}

.hero-card-top {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.chip {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 0.84rem;
    font-weight: 900;
}

.chip-blue {
    background: var(--blue-l);
    color: var(--blue-d);
}

.chip-green {
    background: var(--green-l);
    color: var(--green-d);
}

.dashboard-mock {
    display: grid;
    min-height: 430px;
    overflow: hidden;
    border-radius: 24px;
    background: white;
    border: 1px solid rgba(26, 111, 163, 0.1);
}

.clinic-content.full {
    width: 100%;
}

.mock-content {
    padding: 24px;
    background:
        linear-gradient(180deg, rgba(248,251,253,1), rgba(255,255,255,1));
}

.mock-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.mock-header h3 {
    font-size: 1.25rem;
    font-weight: 900;
    color: var(--dark);
    margin: 0;
}

.mock-header p {
    margin: 6px 0 0;
    color: var(--gray);
    font-weight: 700;
}

.pill {
    background: #eff6ff;
    color: var(--blue-d);
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 0.8rem;
    font-weight: 900;
}

.mock-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 20px;
}

.metric {
    border-radius: 18px;
    background: var(--gray-l);
    padding: 18px 16px;
}

.metric span {
    display: block;
    color: var(--gray);
    font-size: 0.82rem;
    font-weight: 800;
}

.metric strong {
    display: block;
    margin-top: 8px;
    font-size: 1.2rem;
    color: var(--dark);
}

.mock-list {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.mock-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    border: 1px solid rgba(26, 111, 163, 0.08);
    border-radius: 16px;
    padding: 14px 16px;
    box-shadow: 0 6px 20px rgba(15, 34, 54, 0.05);
    font-weight: 800;
}

.status {
    display: inline-flex;
    align-items: center;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 900;
}

.status-success {
    background: var(--green-l);
    color: var(--green-d);
}

.status-warning {
    background: #fff7e6;
    color: #b7791f;
}

.status-info {
    background: var(--blue-l);
    color: var(--blue-d);
}

.section {
    padding: 40px 5% 40px;
}

.section-soft {
    background: linear-gradient(180deg, #f8fbfd, #ffffff);
}

.section-testimonials {
    background: linear-gradient(180deg, #ffffff, #f8fbfd);
}

.section-head {
    max-width: 760px;
    margin-bottom: 34px;
}

.section-head.center {
    text-align: center;
    margin-inline: auto;
    margin-bottom: 40px;
}

.section-head h2 {
    font-size: clamp(2rem, 3.2vw, 3.2rem);
    margin: 0 0 12px;
}

.section-head p,
.lead {
    color: var(--gray);
    line-height: 1.75;
    font-size: 1.02rem;
}

.cards-grid {
    display: grid;
    gap: 20px;
}

.services-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.service-card,
.testimonial-card {
    background: white;
    border-radius: 22px;
    border: 1px solid rgba(26, 111, 163, 0.08);
    padding: 24px;
    box-shadow: var(--shadow);
}

.service-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--blue-l);
    font-size: 1.7rem;
    margin-bottom: 16px;
}

.service-card h3,
.feature-item h3 {
    margin: 0 0 10px;
    color: var(--dark);
    font-size: 1.16rem;
    font-weight: 900;
}

.service-card p,
.feature-item p,
.testimonial-card p {
    margin: 0;
    color: var(--gray);
    line-height: 1.7;
}

.two-col {
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    gap: 34px;
    align-items: start;
}

.feature-list {
    display: grid;
    gap: 18px;
}

.feature-item {
    display: grid;
    grid-template-columns: 46px 1fr;
    gap: 16px;
    align-items: start;
    padding: 20px;
    border-radius: 22px;
    background: white;
    box-shadow: var(--shadow);
    border: 1px solid rgba(26, 111, 163, 0.08);
}

.feature-bullet {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--blue), var(--green));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 1.05rem;
}

.specialties-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: center;
}

.specialty-chip {
    padding: 14px 18px;
    border-radius: 999px;
    background: white;
    border: 1px solid rgba(26, 111, 163, 0.12);
    color: var(--blue-d);
    font-weight: 900;
    box-shadow: var(--shadow);
}

.testimonials-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.testimonial-card {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.testimonial-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.testimonial-meta strong {
    color: var(--dark);
}

.testimonial-meta span {
    color: var(--gray);
    font-weight: 700;
    font-size: 0.92rem;
}

.cta-section {
    padding-top: 30px;
}

.cta-box {
    border-radius: 30px;
    padding: 34px;
    background:
        linear-gradient(135deg, rgba(26,111,163,0.95), rgba(58,170,106,0.88));
    color: white;
    display: grid;
    grid-template-columns: 1.2fr auto;
    gap: 24px;
    align-items: center;
    box-shadow: var(--shadow-lg);
}

.cta-box h2 {
    color: white;
    margin: 10px 0 12px;
    font-size: clamp(2rem, 3vw, 3.1rem);
}

.cta-box p {
    color: rgba(255,255,255,0.9);
    line-height: 1.7;
    max-width: 56ch;
}

.cta-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

footer {
    padding: 54px 5% 28px;
    background: #0f2236;
    color: white;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr 1fr;
    gap: 28px;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.footer-brand p,
.footer-col a {
    color: rgba(255,255,255,0.78);
}

.footer-brand p {
    margin-top: 16px;
    line-height: 1.7;
    max-width: 34ch;
}

.footer-col h4 {
    margin: 0 0 16px;
    font-size: 1rem;
    font-weight: 900;
}

.footer-col ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 10px;
}

.footer-col a {
    text-decoration: none;
    transition: color 0.18s ease;
}

.footer-col a:hover {
    color: white;
}

.footer-bottom {
    padding-top: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    color: rgba(255,255,255,0.72);
    font-size: 0.92rem;
}

#action-btns {
    position: fixed;
    top: 18px;
    right: 18px;
    z-index: 300;
}

#more-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid rgba(15, 34, 54, 0.08);
    background: rgba(255,255,255,0.96);
    color: var(--dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--shadow);
}

#more-popover {
    position: absolute;
    top: 50px;
    right: 0;
    min-width: 210px;
    padding: 8px;
    border-radius: 16px;
    background: white;
    border: 1px solid rgba(15, 34, 54, 0.08);
    box-shadow: var(--shadow-lg);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px);
    transition:
        opacity 0.18s ease,
        transform 0.18s ease,
        visibility 0.18s ease;
}

#more-popover.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.more-item {
    width: 100%;
    min-height: 42px;
    padding: 0 12px;
    border: none;
    border-radius: 12px;
    background: transparent;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
    font-weight: 800;
    cursor: pointer;
}

.more-item:hover {
    background: var(--gray-l);
}

.more-item-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

#copy-toast {
    position: fixed;
    left: 50%;
    bottom: 24px;
    transform: translateX(-50%);
    z-index: 400;
    padding: 12px 16px;
    border-radius: 14px;
    background: rgba(15, 34, 54, 0.94);
    color: white;
    box-shadow: var(--shadow-lg);
    font-weight: 800;
    font-size: 0.92rem;
}

.toast-enter-active,
.toast-leave-active {
    transition:
        opacity 0.22s ease,
        transform 0.22s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(8px);
}

@media (max-width: 1180px) {
    .hero-grid,
    .two-col,
    .cta-box,
    .footer-grid {
        grid-template-columns: 1fr;
    }

    .services-grid,
    .testimonials-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .cta-actions {
        justify-content: flex-start;
    }
}

@media (max-width: 840px) {
    nav {
        padding-inline: 4%;
    }

    .mobile-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .nav-actions {
        display: none;
    }

    .nav-links {
        position: absolute;
        top: 72px;
        left: 4%;
        right: 4%;
        flex-direction: column;
        gap: 0;
        background: rgba(255,255,255,0.98);
        border: 1px solid rgba(26, 111, 163, 0.1);
        border-radius: 18px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition:
            opacity 0.18s ease,
            transform 0.18s ease,
            visibility 0.18s ease;
    }

    .nav-links.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .nav-links li + li {
        border-top: 1px solid rgba(26, 111, 163, 0.08);
    }

    .nav-links a {
        display: block;
        padding: 16px 18px;
    }

    .hero {
        padding-top: 36px;
    }

    .hero-grid {
        min-height: auto;
    }

    .hero-copy h1 {
        max-width: none;
    }

    .services-grid,
    .testimonials-grid {
        grid-template-columns: 1fr;
    }

    #action-btns {
        top: 84px;
        right: 14px;
    }
}

@media (max-width: 560px) {
    .hero-stats,
    .mock-metrics {
        grid-template-columns: 1fr;
    }

    .hero-card,
    .service-card,
    .testimonial-card,
    .feature-item,
    .cta-box {
        padding: 20px;
    }

    .section,
    .hero,
    footer {
        padding-inline: 4%;
    }

    .cta-box {
        border-radius: 24px;
    }
}
</style>
