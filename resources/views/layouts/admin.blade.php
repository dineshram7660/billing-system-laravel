<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarOpen: false }">
        <div class="flex min-h-screen bg-gray-100">

            <!-- Mobile sidebar backdrop -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

            <!-- Sidebar -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 transform overflow-y-auto bg-gray-900 transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            >
                <div class="flex h-16 items-center gap-2 px-5">
                    <x-application-logo class="h-8 w-8 fill-current text-white" />
                    <span class="text-sm font-semibold leading-tight text-white">
                        {{ config('app.name') }}
                    </span>
                </div>

                <nav class="space-y-6 px-3 pb-8">
                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Overview</p>
                        <div class="space-y-0.5">
                            <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                Dashboard
                            </x-sidebar-link>
                        </div>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Master data</p>
                        <div class="space-y-0.5">
                            @can('viewAny', \App\Models\User::class)
                                <x-sidebar-link :href="route('sub-admins.index')" :active="request()->routeIs('sub-admins.*')">
                                    Sub Admin
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Department::class)
                                <x-sidebar-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">
                                    Department
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Designation::class)
                                <x-sidebar-link :href="route('designations.index')" :active="request()->routeIs('designations.*')">
                                    Designation
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Employee::class)
                                <x-sidebar-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                                    Employee
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Product::class)
                                <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                                    Product
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Billing</p>
                        <div class="space-y-0.5">
                            @can('viewAny', \App\Models\Bill::class)
                                <x-sidebar-link :href="route('bills.index')" :active="request()->routeIs('bills.*')">
                                    Bill
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Estimate::class)
                                <x-sidebar-link :href="route('estimates.index')" :active="request()->routeIs('estimates.*')">
                                    Estimate
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Quotation::class)
                                <x-sidebar-link :href="route('quotations.index')" :active="request()->routeIs('quotations.*')">
                                    Quotation
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Reports</p>
                        <div class="space-y-0.5">
                            @can('view-gst-report')
                                <x-sidebar-link :href="route('gst-report.index')" :active="request()->routeIs('gst-report.*')">
                                    GST Report
                                </x-sidebar-link>
                            @endcan
                            @can('view-salary-sheet')
                                <x-sidebar-link :href="route('salary-sheet.index')" :active="request()->routeIs('salary-sheet.*')">
                                    Salary Sheet
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Attendance</p>
                        <div class="space-y-0.5">
                            @can('viewAny', \App\Models\Attendance::class)
                                <x-sidebar-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')">
                                    Attendance
                                </x-sidebar-link>
                            @endcan
                            @can('create', \App\Models\Attendance::class)
                                <x-sidebar-link :href="route('attendance.create')" :active="request()->routeIs('attendance.create')">
                                    Add Attendance
                                </x-sidebar-link>
                                <x-sidebar-link :href="route('attendance.month')" :active="request()->routeIs('attendance.month')">
                                    Add Attendance (All Month)
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>

                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Other</p>
                        <div class="space-y-0.5">
                            @can('send-email')
                                <x-sidebar-link :href="route('email-sends.index')" :active="request()->routeIs('email-sends.*')">
                                    Send Email
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Inquiry::class)
                                <x-sidebar-link :href="route('inquiries.index')" :active="request()->routeIs('inquiries.*')">
                                    Inquiry
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\Account::class)
                                <x-sidebar-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                                    Account
                                </x-sidebar-link>
                            @endcan
                            @can('viewAny', \App\Models\SalarySlip::class)
                                <x-sidebar-link :href="route('salary-slips.index')" :active="request()->routeIs('salary-slips.*')">
                                    Salary Slip
                                </x-sidebar-link>
                            @endcan
                        </div>
                    </div>

                    <div class="border-t border-gray-800 pt-4">
                        <div class="space-y-0.5">
                            <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                                Change Password
                            </x-sidebar-link>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main column -->
            <div class="flex min-w-0 flex-1 flex-col">

                <!-- Top bar -->
                <header class="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden" aria-label="Open menu">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="min-w-0">
                        @isset($header)
                            {{ $header }}
                        @endisset
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
