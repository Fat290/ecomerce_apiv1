<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Onboarding Demo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card { box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08); }
        .log-entry { font-size: 0.85rem; border-left: 3px solid #3b82f6; padding-left: 0.75rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 py-10">
    <div class="max-w-5xl mx-auto space-y-10 px-4">
        <header class="text-center space-y-4">
            <p class="text-sm uppercase tracking-[0.3em] text-blue-500 font-semibold">Realtime Demo</p>
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900">Seller Onboarding Playground</h1>
            <p class="text-slate-600 max-w-2xl mx-auto">
                Use this page to simulate a customer registering, logging in, creating a shop,
                and receiving realtime notifications once an admin approves or rejects the shop.
            </p>
        </header>

        <section class="grid md:grid-cols-2 gap-8">
            <div class="card bg-white rounded-2xl p-6 space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                        <span class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-semibold">1</span>
                        Register
                    </h2>
                    <p class="text-sm text-slate-500">Creates a standard user via <code class="bg-slate-100 px-1 rounded text-xs">POST /api/auth/register</code></p>
                </div>
                <form id="register-form" class="space-y-4">
                    <input type="text" name="name" placeholder="Full name" required class="w-full rounded-lg border-slate-200">
                    <input type="email" name="email" placeholder="Email address" required class="w-full rounded-lg border-slate-200">
                    <input type="password" name="password" placeholder="Password" required class="w-full rounded-lg border-slate-200">
                    <input type="password" name="password_confirmation" placeholder="Confirm password" required class="w-full rounded-lg border-slate-200">
                    <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-500 transition">Register &amp; Auto Login</button>
                </form>
            </div>

            <div class="card bg-white rounded-2xl p-6 space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                        <span class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold">2</span>
                        Login
                    </h2>
                    <p class="text-sm text-slate-500">Uses <code class="bg-slate-100 px-1 rounded text-xs">POST /api/auth/login</code> and stores tokens locally.</p>
                </div>
                <form id="login-form" class="space-y-4">
                    <input type="email" name="email" placeholder="Email address" required class="w-full rounded-lg border-slate-200">
                    <input type="password" name="password" placeholder="Password" required class="w-full rounded-lg border-slate-200">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-500 transition">Login</button>
                </form>
                <div class="bg-slate-100 rounded-xl p-4 text-sm" id="session-info">
                    <p class="font-semibold text-slate-700 mb-2">Session</p>
                    <p class="text-slate-500">Not logged in.</p>
                </div>
            </div>
        </section>

        <section class="card bg-white rounded-2xl p-6 space-y-6">
            <div class="flex flex-col gap-2">
                <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                    <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-semibold">3</span>
                    Create Shop (Pending)
                </h2>
                <p class="text-sm text-slate-500">
                    Sends <code class="bg-slate-100 px-1 rounded text-xs">POST /api/shops</code> with the authenticated user as owner.
                    The shop starts in <span class="font-semibold text-amber-600">pending</span> status until an admin reviews it.
                </p>
            </div>
            <form id="shop-form" class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Shop name" required class="rounded-lg border-slate-200">
                <input type="text" name="address" placeholder="Shop address" required class="rounded-lg border-slate-200">
                <textarea name="description" placeholder="Short description" class="md:col-span-2 rounded-lg border-slate-200" rows="3"></textarea>
                <select name="business_type_id" required class="rounded-lg border-slate-200">
                    <option value="">Choose business type...</option>
                </select>
                <input type="date" name="join_date" class="rounded-lg border-slate-200">
                <div class="md:col-span-2 flex flex-wrap gap-4">
                    <label class="flex flex-col text-sm text-slate-500 gap-1">
                        Logo (optional)
                        <input type="file" name="logo" accept="image/*" class="rounded-lg border-slate-200">
                    </label>
                    <label class="flex flex-col text-sm text-slate-500 gap-1">
                        Banner (optional)
                        <input type="file" name="banner" accept="image/*" class="rounded-lg border-slate-200">
                    </label>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-emerald-600 text-white py-2.5 rounded-lg font-semibold hover:bg-emerald-500 transition">Submit shop for approval</button>
                </div>
            </form>
            <div id="shop-status" class="rounded-xl bg-slate-100 p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-700 mb-2">Shop status</p>
                <p>No shop created yet.</p>
            </div>
        </section>

        <section class="card bg-white rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900 flex items-center gap-2">
                        <span class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-semibold">4</span>
                        Live Updates
                    </h2>
                    <p class="text-sm text-slate-500">
                        Subscribes to <code class="bg-slate-100 px-1 rounded text-xs">private-notifications.{userId}</code>.
                        When an admin approves/rejects, a realtime toast appears below.
                    </p>
                </div>
                <span id="realtime-pill" class="px-3 py-1 rounded-full text-xs bg-slate-200 text-slate-600">Disconnected</span>
            </div>
            <div id="log" class="space-y-2"></div>
        </section>
    </div>

    <script>
        const apiBaseUrl = window.location.origin + '/api';
        const businessTypeSelect = document.querySelector('select[name="business_type_id"]');
        const sessionInfo = document.getElementById('session-info');
        const shopStatusBox = document.getElementById('shop-status');
        const logContainer = document.getElementById('log');
        const realtimePill = document.getElementById('realtime-pill');

        let accessToken = localStorage.getItem('demo_access_token');
        let refreshToken = localStorage.getItem('demo_refresh_token');
        let currentUser = JSON.parse(localStorage.getItem('demo_user') || 'null');
        let currentShop = JSON.parse(localStorage.getItem('demo_shop') || 'null');
        let pusherClient = null;

        const pusherKey = @json(config('broadcasting.connections.pusher.key'));
        const pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster', 'mt1'));

        document.getElementById('register-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = Object.fromEntries(new FormData(event.target));
            try {
                const res = await fetch(apiBaseUrl + '/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(formData),
                });
                const data = await res.json();
                if (!res.ok) throw data;
                log('Registered successfully, logging in...');
                await handleLogin(formData.email, formData.password);
            } catch (error) {
                handleError('Registration failed', error);
            }
        });

        document.getElementById('login-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = Object.fromEntries(new FormData(event.target));
            await handleLogin(formData.email, formData.password);
        });

        document.getElementById('shop-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!accessToken) {
                log('Please login first.', true);
                return;
            }
            const form = event.target;
            const payload = new FormData(form);

            try {
                const res = await fetch(apiBaseUrl + '/shops', {
                    method: 'POST',
                    headers: authHeaders({ asJson: false }),
                    body: payload,
                });
                const data = await res.json();
                if (!res.ok) throw data;
                currentShop = data.data;
                localStorage.setItem('demo_shop', JSON.stringify(currentShop));
                renderShopStatus();
                log('Shop submitted successfully. Waiting for admin approval...');
            } catch (error) {
                handleError('Shop creation failed', error);
            }
        });

        async function handleLogin(email, password) {
            try {
                const res = await fetch(apiBaseUrl + '/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, password }),
                });
                const data = await res.json();
                if (!res.ok) throw data;

                accessToken = data.data.access_token;
                refreshToken = data.data.refresh_token;
                currentUser = data.data.user;

                localStorage.setItem('demo_access_token', accessToken);
                localStorage.setItem('demo_refresh_token', refreshToken);
                localStorage.setItem('demo_user', JSON.stringify(currentUser));

                renderSession();
                connectRealtime();
                log('Logged in successfully.');
                await hydrateShop();
            } catch (error) {
                handleError('Login failed', error);
            }
        }

        function authHeaders(options = {}) {
            const { asJson = true } = options;
            const headers = {
                'Authorization': `Bearer ${accessToken}`,
                'Accept': 'application/json',
            };
            if (asJson) {
                headers['Content-Type'] = 'application/json';
            }
            return headers;
        }

        function renderSession() {
            if (!currentUser) {
                sessionInfo.innerHTML = '<p class="text-slate-500">Not logged in.</p>';
                return;
            }
            sessionInfo.innerHTML = `
                <p class="text-slate-700 font-semibold">${currentUser.name}</p>
                <p class="text-slate-500 text-sm">${currentUser.email}</p>
                <p class="text-xs text-slate-400 mt-2">User ID: ${currentUser.id}, Role: ${currentUser.role}</p>
                <button class="mt-3 text-xs text-red-500 hover:underline" onclick="logout()">Logout</button>
            `;
        }

        async function loadBusinessTypes() {
            try {
                const res = await fetch(apiBaseUrl + '/business-types');
                const data = await res.json();
                if (!res.ok) throw data;
                if (Array.isArray(data.data)) {
                    data.data.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        businessTypeSelect.appendChild(option);
                    });
                }
            } catch (error) {
                log('Failed to load business types. ' + (error.message || ''), true);
            }
        }

        function renderShopStatus() {
            if (!currentShop) {
                shopStatusBox.innerHTML = '<p class="text-slate-500">No shop created yet.</p>';
                return;
            }
            shopStatusBox.innerHTML = `
                <p class="font-semibold text-slate-700">${currentShop.name}</p>
                <p class="text-sm text-slate-500 mb-2">${currentShop.description || 'No description'}</p>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${badgeColor(currentShop.status)}">
                    ${currentShop.status}
                </span>
                <p class="text-xs text-slate-400 mt-2">Shop ID: ${currentShop.id}</p>
            `;
        }

        function badgeColor(status) {
            switch (status) {
                case 'active': return 'bg-emerald-100 text-emerald-700';
                case 'banned': return 'bg-rose-100 text-rose-700';
                default: return 'bg-amber-100 text-amber-700';
            }
        }

        async function hydrateShop() {
            if (!currentShop) {
                renderShopStatus();
                return;
            }

            try {
                const res = await fetch(`${apiBaseUrl}/shops/${currentShop.id}`);
                const data = await res.json();
                if (res.ok && data.data) {
                    currentShop = data.data;
                    localStorage.setItem('demo_shop', JSON.stringify(currentShop));
                    renderShopStatus();
                }
            } catch (error) {
                console.error(error);
            }
        }

        function connectRealtime() {
            if (!pusherKey || !currentUser || !accessToken) {
                realtimePill.textContent = 'Disconnected';
                realtimePill.className = 'px-3 py-1 rounded-full text-xs bg-slate-200 text-slate-600';
                return;
            }

            if (pusherClient) {
                pusherClient.disconnect();
            }

            realtimePill.textContent = 'Connecting...';
            realtimePill.className = 'px-3 py-1 rounded-full text-xs bg-amber-100 text-amber-700';

            pusherClient = new Pusher(pusherKey, {
                cluster: pusherCluster || 'mt1',
                authEndpoint: `${apiBaseUrl}/broadcasting/auth`,
                auth: {
                    headers: authHeaders({ asJson: false }),
                },
            });

            const channel = pusherClient.subscribe(`private-notifications.${currentUser.id}`);

            channel.bind('pusher:subscription_succeeded', () => {
                realtimePill.textContent = 'Live';
                realtimePill.className = 'px-3 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700';
                log('Realtime connection established.');
            });

            channel.bind('pusher:subscription_error', status => {
                realtimePill.textContent = 'Error';
                realtimePill.className = 'px-3 py-1 rounded-full text-xs bg-rose-100 text-rose-700';
                log(`Realtime subscription error (${status}).`, true);
            });

            channel.bind('notification.created', payload => {
                const title = payload.title || 'Notification';
                log(`${title}: ${payload.message}`, payload.type !== 'information');

                if (payload.data && payload.data.shop_id && currentShop && payload.data.shop_id === currentShop.id) {
                    currentShop.status = payload.data.status;
                    renderShopStatus();
                }
            });
        }

        function log(message, isError = false) {
            const entry = document.createElement('div');
            entry.className = `log-entry ${isError ? 'text-rose-600' : 'text-slate-700'}`;
            const timestamp = new Date().toLocaleTimeString();
            entry.textContent = `[${timestamp}] ${message}`;
            logContainer.prepend(entry);
        }

        function handleError(context, payload) {
            console.error(context, payload);
            const details = payload?.message || payload?.error || 'Unknown error';
            log(`${context}: ${details}`, true);
        }

        function logout() {
            accessToken = null;
            refreshToken = null;
            currentUser = null;
            currentShop = null;
            localStorage.removeItem('demo_access_token');
            localStorage.removeItem('demo_refresh_token');
            localStorage.removeItem('demo_user');
            localStorage.removeItem('demo_shop');
            if (pusherClient) {
                pusherClient.disconnect();
                pusherClient = null;
            }
            renderSession();
            renderShopStatus();
            realtimePill.textContent = 'Disconnected';
            realtimePill.className = 'px-3 py-1 rounded-full text-xs bg-slate-200 text-slate-600';
            log('Logged out.');
        }

        window.logout = logout;

        async function bootstrap() {
            renderSession();
            renderShopStatus();
            await loadBusinessTypes();
            if (accessToken && currentUser) {
                connectRealtime();
                await hydrateShop();
            }
        }

        bootstrap();
    </script>
</body>
</html>
