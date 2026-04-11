{{-- Global Toast Notification Component --}}
{{-- Listens to Livewire 'notify' events and session flash messages --}}
<div
    x-data="{
        notifications: [],
        counter: 0,

        add(message, type = 'success') {
            const id = ++this.counter;
            this.notifications.push({ id, message, type, show: false });

            // Trigger enter animation
            this.$nextTick(() => {
                const idx = this.notifications.findIndex(n => n.id === id);
                if (idx !== -1) this.notifications[idx].show = true;
            });

            // Auto-remove after 4 seconds
            setTimeout(() => this.remove(id), 4000);
        },

        remove(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx !== -1) {
                this.notifications[idx].show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 400);
            }
        },

        iconPath(type) {
            switch(type) {
                case 'success':
                    return 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
                case 'error':
                    return 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z';
                case 'warning':
                    return 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
                case 'info':
                    return 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
                default:
                    return 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
            }
        },

        colorClasses(type) {
            switch(type) {
                case 'success':
                    return 'bg-emerald-50 border-emerald-200 text-emerald-800';
                case 'error':
                    return 'bg-red-50 border-red-200 text-red-800';
                case 'warning':
                    return 'bg-amber-50 border-amber-200 text-amber-800';
                case 'info':
                    return 'bg-blue-50 border-blue-200 text-blue-800';
                default:
                    return 'bg-emerald-50 border-emerald-200 text-emerald-800';
            }
        },

        iconColor(type) {
            switch(type) {
                case 'success': return 'text-emerald-500';
                case 'error': return 'text-red-500';
                case 'warning': return 'text-amber-500';
                case 'info': return 'text-blue-500';
                default: return 'text-emerald-500';
            }
        },

        progressColor(type) {
            switch(type) {
                case 'success': return 'bg-emerald-400';
                case 'error': return 'bg-red-400';
                case 'warning': return 'bg-amber-400';
                case 'info': return 'bg-blue-400';
                default: return 'bg-emerald-400';
            }
        }
    }"

    {{-- Listen to Livewire event --}}
    x-on:notify.window="add($event.detail.message, $event.detail.type || 'success')"

    {{-- Session flash messages on page load --}}
    x-init="
        @if(session('success'))
            add('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            add('{{ session('error') }}', 'error');
        @endif
        @if(session('warning'))
            add('{{ session('warning') }}', 'warning');
        @endif
        @if(session('info'))
            add('{{ session('info') }}', 'info');
        @endif
    "

    class="fixed top-0 left-0 right-0 z-[99999] flex flex-col items-center pointer-events-none px-4 pt-3 space-y-2"
    style="padding-top: max(12px, env(safe-area-inset-top));"
>
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="notification.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="pointer-events-auto w-full max-w-sm"
        >
            <div
                :class="colorClasses(notification.type)"
                class="relative overflow-hidden rounded-2xl border shadow-lg backdrop-blur-sm"
            >
                <div class="flex items-start gap-3 px-4 py-3">
                    {{-- Icon --}}
                    <svg :class="iconColor(notification.type)" class="w-5 h-5 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(notification.type)" />
                    </svg>

                    {{-- Message --}}
                    <p class="text-sm font-semibold flex-1 leading-snug" x-text="notification.message"></p>

                    {{-- Close button --}}
                    <button @click="remove(notification.id)" class="shrink-0 -mr-1 -mt-0.5 p-1 rounded-lg opacity-50 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Progress bar --}}
                <div class="h-0.5 w-full bg-black/5">
                    <div
                        :class="progressColor(notification.type)"
                        class="h-full"
                        x-init="$nextTick(() => { $el.style.transition = 'width 4s linear'; $el.style.width = '0%'; })"
                        style="width: 100%;"
                    ></div>
                </div>
            </div>
        </div>
    </template>
</div>
