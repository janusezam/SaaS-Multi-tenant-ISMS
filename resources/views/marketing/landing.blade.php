<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ISMS | Intramurals SaaS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/isms-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Urbanist', sans-serif; }
    </style>
</head>
<body class="bg-[#0b1120] text-slate-200 antialiased selection:bg-indigo-500/30">
    <!-- Background Gradients -->
    <div class="fixed inset-0 -z-10 h-full w-full bg-[#0b1120]">
        <div class="absolute bottom-0 left-[-20%] right-0 top-[-10%] h-[500px] w-[500px] rounded-full bg-[radial-gradient(circle_farthest-side,rgba(79,70,229,0.15),rgba(255,255,255,0))]"></div>
        <div class="absolute bottom-[-20%] right-[-10%] h-[600px] w-[600px] rounded-full bg-[radial-gradient(circle_farthest-side,rgba(14,165,233,0.1),rgba(255,255,255,0))]"></div>
    </div>

    <!-- Header -->
    <header>
        <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="{{ route('public.landing') }}" class="-m-1.5 p-1.5 flex items-center gap-2">
                    <img class="h-10 w-auto" src="{{ asset('images/isms-logo.png') }}" alt="ISMS Logo">
                    <span class="text-xl font-bold text-white tracking-tight">ISMS</span>
                </a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="#features" class="text-sm font-semibold leading-6 text-slate-300 hover:text-white transition-colors">Features</a>
                <a href="#benefits" class="text-sm font-semibold leading-6 text-slate-300 hover:text-white transition-colors">Benefits</a>
                <a href="{{ route('public.pricing') }}" class="text-sm font-semibold leading-6 text-slate-300 hover:text-white transition-colors">Pricing</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                <a href="{{ route('central.login') }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all hover:shadow-[0_0_20px_rgba(79,70,229,0.4)]">Admin Login</a>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <div class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-4xl py-32 sm:py-48 lg:py-56 text-center">
                <h1 class="text-5xl font-bold tracking-tight text-white sm:text-7xl">
                    Run every school league <br/>from <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">one platform.</span>
                </h1>
                <p class="mt-8 text-lg leading-8 text-slate-400 max-w-2xl mx-auto">
                    ISMS gives each school a dedicated workspace to run intramurals end-to-end on Basic, then upgrade to Pro for analytics, bracket automation, and report exports.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('public.pricing') }}#subscribe" class="rounded-full bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all hover:shadow-[0_0_20px_rgba(79,70,229,0.4)]">
                        Get started
                    </a>
                    <a href="{{ route('public.pricing') }}" class="rounded-full bg-slate-800/80 ring-1 ring-white/10 px-8 py-3.5 text-sm font-semibold text-white hover:bg-slate-800 transition-all hover:ring-white/20">
                        Compare Basic vs Pro <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats / Proof Section -->
        <div class="mx-auto max-w-7xl px-6 lg:px-8 pb-24">
            <div class="grid grid-cols-1 gap-y-16 gap-x-8 text-center md:grid-cols-3 border-y border-white/5 py-12">
                <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                    <dt class="text-base leading-7 text-slate-400">The lowest price</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-white">Affordable</dd>
                </div>
                <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                    <dt class="text-base leading-7 text-slate-400">The fastest on the market</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-white">Instant Setup</dd>
                </div>
                <div class="mx-auto flex max-w-xs flex-col gap-y-2">
                    <dt class="text-base leading-7 text-slate-400">The most loved</dt>
                    <dd class="order-first text-3xl font-semibold tracking-tight text-white">99% Rating</dd>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="mx-auto max-w-7xl px-6 lg:px-8 py-24 sm:py-32">
            <div class="max-w-2xl">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">A technology-first approach to intramural sports</h2>
                <p class="mt-4 text-lg text-slate-400">Everything you need to manage sports events, teams, and players efficiently in a single, dedicated workspace.</p>
            </div>
            
            <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Feature 1 -->
                <div class="rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 transition-all hover:bg-slate-800/80">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-lg font-semibold text-white">Sports & Venues</h3>
                    <p class="mb-4 text-sm text-slate-400">Manage all sports categories and playing venues efficiently from a centralized dashboard.</p>
                    <a href="{{ route('public.pricing') }}" class="text-sm font-medium text-indigo-400 hover:text-indigo-300">Read more</a>
                </div>
                
                <!-- Feature 2 -->
                <div class="rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 transition-all hover:bg-slate-800/80">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-400 ring-1 ring-cyan-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-lg font-semibold text-white">Teams & Players</h3>
                    <p class="mb-4 text-sm text-slate-400">Register and organize teams and players seamlessly with dedicated management tools.</p>
                    <a href="{{ route('public.pricing') }}" class="text-sm font-medium text-cyan-400 hover:text-cyan-300">Read more</a>
                </div>

                <!-- Feature 3 -->
                <div class="rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 transition-all hover:bg-slate-800/80">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400 ring-1 ring-amber-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-lg font-semibold text-white">Schedules & Results</h3>
                    <p class="mb-4 text-sm text-slate-400">Automated scheduling, real-time match results tracking, and transparent standings.</p>
                    <a href="{{ route('public.pricing') }}" class="text-sm font-medium text-amber-400 hover:text-amber-300">Read more</a>
                </div>

                <!-- Feature 4 -->
                <div class="rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 transition-all hover:bg-slate-800/80">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400 ring-1 ring-rose-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-lg font-semibold text-white">Pro Analytics</h3>
                    <p class="mb-4 text-sm text-slate-400">Advanced bracket generation, bracket audits, and comprehensive CSV/PDF exports.</p>
                    <a href="{{ route('public.pricing') }}" class="text-sm font-medium text-rose-400 hover:text-rose-300">Read more</a>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div id="benefits" class="mx-auto max-w-7xl px-6 lg:px-8 py-24 sm:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Management is carried out by passionate administrators</h2>
                    <p class="mt-4 text-lg text-slate-400">ISMS is tailored specifically for school intramural requirements. Our platform empowers coordinators to deliver flawless sporting events without the administrative headache.</p>
                    
                    <div class="mt-8 space-y-6">
                        <div class="flex gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-500/10 ring-1 ring-indigo-500/20">
                                <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Centralized Subscriptions</h3>
                                <p class="mt-1 text-slate-400">Manage school billing and domain routing effectively.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 border-t border-white/5 pt-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20">
                                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Isolated Tenant Architecture</h3>
                                <p class="mt-1 text-slate-400">Dedicated database and environment for each school ensuring data privacy.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-indigo-500/20 to-cyan-500/20 blur-2xl"></div>
                    <div class="relative rounded-3xl bg-slate-900 border border-white/10 p-8 shadow-2xl">
                        <!-- Abstract Visual representing data/chart -->
                        <div class="flex justify-center items-center h-64">
                            <div class="relative w-48 h-48 rounded-full border-[16px] border-slate-800 border-t-indigo-500 border-r-cyan-500 shadow-[0_0_40px_rgba(79,70,229,0.2)] transform rotate-45"></div>
                            <div class="absolute bg-white/5 w-64 h-64 rounded-full blur-3xl"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Developers -->
        <div class="mx-auto max-w-7xl px-6 lg:px-8 py-24 sm:py-32">
            <h2 class="text-center text-3xl font-bold tracking-tight text-white sm:text-4xl">Developers behind SaaS ISMS</h2>
            <p class="mx-auto mt-4 max-w-2xl text-center text-lg text-slate-400">The passionate team dedicated to revolutionizing school intramural management.</p>
            
            <div class="mx-auto mt-16 grid max-w-7xl grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Dev 1 -->
                <div class="flex flex-col justify-between rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 text-center items-center hover:bg-slate-800/80 transition-all">
                    <img src="{{ asset('images/janus.jpg.jpg') }}" alt="Janus Ezam Tagud" class="h-24 w-24 rounded-full object-cover mb-4 ring-2 ring-indigo-500/50" onerror="this.src='https://ui-avatars.com/api/?name=Janus+Ezam+Tagud&background=4f46e5&color=fff'">
                    <h3 class="text-lg font-semibold text-white">Janus Ezam Tagud</h3>
                    <p class="text-sm text-indigo-400 mb-4">Lead Developer / Founder</p>
                    <p class="text-sm text-slate-300">Visionary behind SaaS ISMS, orchestrating the system architecture and core multi-tenant engine.</p>
                </div>
                
                <!-- Dev 2 -->
                <div class="flex flex-col justify-between rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 text-center items-center hover:bg-slate-800/80 transition-all">
                    <img src="{{ asset('images/sam.jpg.jpg') }}" alt="Sam Anthony Rey" class="h-24 w-24 rounded-full object-cover mb-4 ring-2 ring-cyan-500/50" onerror="this.src='https://ui-avatars.com/api/?name=Sam+Anthony+Rey&background=0ea5e9&color=fff'">
                    <h3 class="text-lg font-semibold text-white">Sam Anthony Rey</h3>
                    <p class="text-sm text-cyan-400 mb-4">Full Stack Developer</p>
                    <p class="text-sm text-slate-300">Specializes in responsive UI/UX and seamless integration of backend services for an optimal user experience.</p>
                </div>

                <!-- Dev 3 -->
                <div class="flex flex-col justify-between rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 text-center items-center hover:bg-slate-800/80 transition-all">
                    <img src="{{ asset('images/armando.jpg.jpg') }}" alt="Armando Sagayoc" class="h-24 w-24 rounded-full object-cover mb-4 ring-2 ring-amber-500/50" onerror="this.src='https://ui-avatars.com/api/?name=Armando+Sagayoc&background=f59e0b&color=fff'">
                    <h3 class="text-lg font-semibold text-white">Armando Sagayoc</h3>
                    <p class="text-sm text-amber-400 mb-4">Backend Developer</p>
                    <p class="text-sm text-slate-300">Dedicated to crafting robust database schemas, secure tenant isolation, and API performance.</p>
                </div>
            </div>
            
            <div class="mx-auto mt-8 grid max-w-4xl grid-cols-1 gap-8 sm:grid-cols-2">
                <!-- Dev 4 -->
                <div class="flex flex-col justify-between rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 text-center items-center hover:bg-slate-800/80 transition-all">
                    <img src="{{ asset('images/kate.jpg.jpg') }}" alt="Kate Alysabelle" class="h-24 w-24 rounded-full object-cover mb-4 ring-2 ring-rose-500/50" onerror="this.src='https://ui-avatars.com/api/?name=Kate+Alysabelle&background=f43f5e&color=fff'">
                    <h3 class="text-lg font-semibold text-white">Kate Alysabelle</h3>
                    <p class="text-sm text-rose-400 mb-4">Frontend Developer</p>
                    <p class="text-sm text-slate-300">Transforms complex data into intuitive, accessible dashboards and beautiful tournament brackets.</p>
                </div>

                <!-- Dev 5 -->
                <div class="flex flex-col justify-between rounded-3xl bg-slate-800/50 p-8 ring-1 ring-white/10 text-center items-center hover:bg-slate-800/80 transition-all">
                    <img src="{{ asset('images/milcky.jpg.jpg') }}" alt="Milcky Jhones Francisco" class="h-24 w-24 rounded-full object-cover mb-4 ring-2 ring-emerald-500/50" onerror="this.src='https://ui-avatars.com/api/?name=Milcky+Jhones+Francisco&background=10b981&color=fff'">
                    <h3 class="text-lg font-semibold text-white">Milcky Jhones Francisco</h3>
                    <p class="text-sm text-emerald-400 mb-4">Quality Assurance & DevOps</p>
                    <p class="text-sm text-slate-300">Ensures platform reliability, orchestrates deployments, and maintains stringent software quality standards.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="relative isolate mt-16 px-6 py-32 sm:mt-24 sm:py-40 lg:px-8">
            <div class="absolute inset-x-0 top-1/2 -z-10 -translate-y-1/2 transform-gpu overflow-hidden opacity-30 blur-3xl" aria-hidden="true">
                <div class="ml-[max(50%,38rem)] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
            <div class="mx-auto max-w-2xl text-center">
                <div class="flex justify-center -space-x-2 mb-6">
                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-[#0b1120] object-cover" src="{{ asset('images/janus.jpg.jpg') }}" onerror="this.src='https://ui-avatars.com/api/?name=Janus&background=4f46e5&color=fff'" alt="Janus">
                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-[#0b1120] object-cover" src="{{ asset('images/sam.jpg.jpg') }}" onerror="this.src='https://ui-avatars.com/api/?name=Sam&background=0ea5e9&color=fff'" alt="Sam">
                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-[#0b1120] object-cover" src="{{ asset('images/armando.jpg.jpg') }}" onerror="this.src='https://ui-avatars.com/api/?name=Armando&background=f59e0b&color=fff'" alt="Armando">
                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-[#0b1120] object-cover" src="{{ asset('images/kate.jpg.jpg') }}" onerror="this.src='https://ui-avatars.com/api/?name=Kate&background=f43f5e&color=fff'" alt="Kate">
                    <img class="inline-block h-10 w-10 rounded-full ring-2 ring-[#0b1120] object-cover" src="{{ asset('images/milcky.jpg.jpg') }}" onerror="this.src='https://ui-avatars.com/api/?name=Milcky&background=10b981&color=fff'" alt="Milcky">
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Get Started now</h2>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-slate-400">Be part of the schools around the world using ISMS to modernise their sports programs.</p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('public.pricing') }}#subscribe" class="rounded-full bg-indigo-600 px-8 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">Get Started</a>
                    <a href="#features" class="rounded-full bg-slate-800/80 ring-1 ring-white/10 px-8 py-3.5 text-sm font-semibold text-white hover:bg-slate-800 transition-all">More about</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-24 border-t border-white/10 py-12">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                <div class="flex flex-col items-center md:items-start gap-2">
                    <div class="flex items-center gap-2">
                        <img class="h-8 w-auto" src="{{ asset('images/isms-logo.png') }}" alt="ISMS Logo">
                        <span class="text-xl font-bold text-white tracking-tight">ISMS</span>
                    </div>
                    <p class="text-sm text-slate-400 mt-2 text-center md:text-left">Intramurals Sports Management System. Revolutionizing school sports events.</p>
                </div>
                
                <div class="flex flex-col items-center gap-2">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-2">Contact Us</h3>
                    <a href="tel:+639614527106" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                        📞 +63 961 452 7106
                    </a>
                    <a href="mailto:janusezam@gmail.com" class="text-slate-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                        📧 janusezam@gmail.com
                    </a>
                </div>

                <div class="flex flex-col items-center md:items-end gap-3">
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Socials</h3>
                    <div class="flex gap-4">
                        <a href="https://www.facebook.com/share/1E2h3RELgb/" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform" title="Facebook">
                            <img src="{{ asset('images/facebook.png') }}" alt="Facebook" class="h-8 w-8 object-contain">
                        </a>
                        <a href="https://github.com/janusezam" target="_blank" rel="noopener noreferrer" class="hover:scale-110 transition-transform" title="GitHub">
                            <img src="{{ asset('images/social.png') }}" alt="GitHub" class="h-8 w-8 object-contain rounded-full bg-white">
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-12 border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-slate-500">&copy; {{ date('Y') }} Intramurals Sports Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
