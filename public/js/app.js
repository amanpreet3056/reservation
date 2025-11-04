(() => {
    'use strict';

    if (typeof window !== 'undefined' && window.axios) {
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function showFeedback(container, message, tone = 'neutral') {
        if (!container) {
            return;
        }

        container.textContent = message ?? '';
        container.classList.remove('text-neutral-400', 'text-emerald-300', 'text-emerald-400', 'text-amber-300', 'text-red-400', 'text-rose-400', 'font-semibold');

        const tones = {
            neutral: 'text-neutral-400',
            success: 'text-emerald-300',
            warning: 'text-amber-300',
            error: 'text-rose-400',
        };

        container.classList.add(tones[tone] ?? tones.neutral);
    }

    function toggleButtonState(button, isLoading) {
        if (!button) {
            return;
        }

        button.disabled = isLoading;

        if (isLoading) {
            button.dataset.originalText = button.textContent;
            button.textContent = 'Processing...';
            button.classList.add('opacity-70', 'cursor-not-allowed');
        } else {
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
            }
            button.classList.remove('opacity-70', 'cursor-not-allowed');
        }
    }

    function serializeForm(form) {
        const formData = new FormData(form);
        const payload = {};

        formData.forEach((value, rawKey) => {
            const key = rawKey.endsWith('[]') ? rawKey.slice(0, -2) : rawKey;

            if (rawKey.endsWith('[]')) {
                if (!Array.isArray(payload[key])) {
                    payload[key] = [];
                }
                payload[key].push(value);
                return;
            }

            if (Object.prototype.hasOwnProperty.call(payload, key)) {
                if (!Array.isArray(payload[key])) {
                    payload[key] = [payload[key]];
                }
                payload[key].push(value);
                return;
            }

            payload[key] = value;
        });

        delete payload._token;

        return payload;
    }

    function disableForm(form, reason, feedback) {
        if (!form) {
            return;
        }

        const fields = form.querySelectorAll('input, select, textarea, button');
        fields.forEach((field) => {
            field.disabled = true;
        });

        showFeedback(feedback, reason || 'Online reservations are currently disabled.', 'warning');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const app = document.querySelector('[data-reservation-app]');
        if (!app) {
            return;
        }

        const form = app.querySelector('[data-reservation-form]');
        const successPanel = app.querySelector('[data-success-panel]');\n        const globalFeedback = app.querySelector('[data-global-feedback]');
        const timelineContainer = successPanel?.querySelector('[data-timeline-container]');
        const timelineList = successPanel?.querySelector('[data-timeline]');
        const calendarContainer = successPanel?.querySelector('[data-calendar-container]');
        const googleCalendarLink = successPanel?.querySelector('[data-calendar-google]');
        const icsCalendarLink = successPanel?.querySelector('[data-calendar-ics]');
        const feedback = app.querySelector('[data-feedback]');
        const submitButton = form?.querySelector('[data-action="submit"]');
        const timeSelect = form?.querySelector('[data-time-select]');
        const availabilityFeedback = app.querySelector('[data-availability-feedback]');
        const dateInput = form?.querySelector('#reservation_date');
        const partySelect = form?.querySelector('#number_of_people');

        const submitUrl = app.dataset.submitUrl ?? '';
        const availabilityUrl = app.dataset.availabilityUrl ?? '';
        let availabilityController;

        const optionLabels = new Map();
        if (timeSelect) {
            Array.from(timeSelect.options).forEach((option) => {
                optionLabels.set(option.value, option.textContent);
            });
        }

        const updateAvailabilityUI = (slots) => {
            if (!timeSelect) {
                return;
            }

            let firstAvailable = null;
            const slotMap = new Map(slots.map((slot) => [slot.time, slot]));

            Array.from(timeSelect.options).forEach((option) => {
                const slot = slotMap.get(option.value);
                const baseLabel = optionLabels.get(option.value) ?? option.textContent;

                if (!slot) {
                    option.disabled = false;
                    option.textContent = baseLabel;
                    return;
                }

                option.disabled = !slot.available;
                option.textContent = slot.available ? baseLabel : `${baseLabel} - Fully booked`;
                option.dataset.availableTables = slot.available_tables ?? 0;

                if (slot.available && !firstAvailable) {
                    firstAvailable = option.value;
                }
            });

            if (timeSelect.value && timeSelect.selectedOptions[0]?.disabled) {
                if (firstAvailable) {
                    timeSelect.value = firstAvailable;
                } else {
                    timeSelect.value = '';
                }
            }

            if (!timeSelect.value && firstAvailable) {
                timeSelect.value = firstAvailable;
            }

            const availableCount = slots.filter((slot) => slot.available).length;
            if (availabilityFeedback) {
                if (availableCount === 0) {
                    showFeedback(availabilityFeedback, 'No seats available at the selected date. Please pick another day.', 'warning');
                } else {
                    showFeedback(
                        availabilityFeedback,
                        availableCount === 1
                            ? 'Only one slot left - secure it soon!'
                            : `${availableCount} time slots are available for your party.`,
                        availableCount <= 2 ? 'warning' : 'neutral',
                    );
                }
            }

            if (submitButton) {
                submitButton.disabled = availableCount === 0;
            }
        };

        const fetchAvailability = async () => {
            if (!availabilityUrl || !dateInput || !partySelect || !timeSelect) {
                return;
            }

            const dateValue = dateInput.value?.trim();
            const partyValue = partySelect.value;

            if (!dateValue || !partyValue) {
                return;
            }

            if (availabilityController) {
                availabilityController.abort();
            }
            availabilityController = new AbortController();

        if (availabilityFeedback) {
                showFeedback(availabilityFeedback, 'Checking availability...', 'neutral');
            }

            try {
                const params = new URLSearchParams({
                    date: dateValue,
                    party_size: partyValue,
                });

                const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                    },
                    signal: availabilityController.signal,
                });

                if (!response.ok) {
                    throw new Error('Unable to check availability right now.');
                }

                const data = await response.json();

                updateAvailabilityUI(data.slots ?? []);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                console.error(error);
                if (availabilityFeedback) {
                    showFeedback(
                        availabilityFeedback,
                        error?.message || 'We could not check availability. Please try again shortly.',
                        'error',
                    );
                }

                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        };

        if (app.dataset.bookingDisabled === 'true') {
            disableForm(form, app.dataset.bookingDisabledMessage, feedback);
            return;
        }

        dateInput?.addEventListener('change', fetchAvailability);
        partySelect?.addEventListener('change', fetchAvailability);
        fetchAvailability();

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!form || !submitUrl) {
                return;
            }

            showFeedback(feedback, '');\n        showFeedback(globalFeedback, '');
            toggleButtonState(submitButton, true);

            try {
                const payload = serializeForm(form);

                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                    body: JSON.stringify(payload),
                });

                let data = {};
                try {
                    data = await response.json();
                } catch (parseError) {
                    // ignore parse issues; data stays empty
                }

                if (!response.ok) {
                    const firstError = data?.errors ? Object.values(data.errors).flat()[0] : data?.message;
                    throw new Error(firstError || 'Please fill in the required fields.');
                }

                const successMessage =
                    data?.message || 'Thank you! Your reservation request has been submitted. We will reach out soon.';

                showFeedback(feedback, successMessage, 'success');\n                showFeedback(globalFeedback, successMessage, 'success');

                if (successPanel) {
                    const messageTarget = successPanel.querySelector('[data-success-message]');
                    if (messageTarget) {
                        messageTarget.textContent = successMessage;
                    }

                    if (timelineList) {
                        timelineList.innerHTML = '';
                        const events = Array.isArray(data?.timeline) ? data.timeline : [];
                        if (events.length) {
                            timelineContainer?.removeAttribute('hidden');
                            events.forEach((event) => {
                                const item = document.createElement('li');
                                item.className = 'flex items-start gap-3 text-sm text-emerald-100';
                                const bullet = document.createElement('span');
                                bullet.className = 'mt-1 h-2 w-2 rounded-full bg-emerald-400 flex-shrink-0';
                                const body = document.createElement('span');
                                const when = event.timestamp ? new Date(event.timestamp) : null;
                                const formatted = when ? when.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) : '';
                                body.innerHTML = `<strong class="font-semibold">${event.label ?? ''}</strong>${formatted ? `<br><span class="text-emerald-200/80 text-xs">${formatted}</span>` : ''}`;
                                item.appendChild(bullet);
                                item.appendChild(body);
                                timelineList.appendChild(item);
                            });
                        } else {
                            timelineContainer?.setAttribute('hidden', 'hidden');
                        }
                    }

                    if (calendarContainer) {
                        const googleUrl = data?.calendar?.google ?? null;
                        const icsUrl = data?.calendar?.ics ?? null;

                        if (googleCalendarLink) {
                            if (googleUrl) {
                                googleCalendarLink.href = googleUrl;
                                googleCalendarLink.removeAttribute('hidden');
                            } else {
                                googleCalendarLink.setAttribute('hidden', 'hidden');
                            }
                        }

                        if (icsCalendarLink) {\n                        if (icsUrl) {\n                            icsCalendarLink.href = icsUrl;\n                            icsCalendarLink.setAttribute('download', data?.calendar?.filename ?? 'reservation.ics');\n                            icsCalendarLink.removeAttribute('hidden');\n                        } else {\n                            icsCalendarLink.setAttribute('hidden', 'hidden');\n                        }\n                    }

                        if (googleUrl || icsUrl) {
                            calendarContainer.removeAttribute('hidden');
                        } else {
                            calendarContainer.setAttribute('hidden', 'hidden');
                        }
                    }

                    form.classList.add('hidden');
                    successPanel.classList.remove('hidden');
                    successPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } catch (error) {
                console.error(error);
                showFeedback(
                    feedback,
                    error?.message || 'Something went wrong. Please try again.',
                    'error',
                );
            } finally {
                toggleButtonState(submitButton, false);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-status-select]').forEach((select) => {
            select.addEventListener('change', (event) => {
                const target = event.currentTarget;
                const form = target.closest('[data-status-form]');
                if (!form) {
                    return;
                }

                const previous = target.dataset.currentStatus;
                const selected = target.value;
                const cancelReasonInput = form.querySelector('input[name="cancel_reason"]');

                if (selected === 'cancelled') {
                    const reason = window.prompt('Please provide a cancellation reason (optional).', '');

                    if (reason === null) {
                        target.value = previous;
                        return;
                    }

                    if (cancelReasonInput) {
                        cancelReasonInput.value = reason.trim() !== '' ? reason.trim() : 'Cancelled via dashboard quick action.';
                    }
                } else if (cancelReasonInput) {
                    cancelReasonInput.value = '';
                }

                target.dataset.currentStatus = selected;
                form.submit();
            });
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.querySelector('[data-booking-modal]');
        if (!modal) {
            return;
        }

        const sections = {
            pause: modal.querySelector('[data-mode-section="pause"]'),
            resume: modal.querySelector('[data-mode-section="resume"]'),
        };

        const closureField = modal.querySelector('[data-closure-field]');

        const showModal = (mode) => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            Object.entries(sections).forEach(([key, element]) => {
                if (!element) {
                    return;
                }
                element.classList.toggle('hidden', key !== mode);
            });

            if (mode === 'resume' && closureField) {
                closureField.closest('form')?.classList.remove('hidden');
            }
        };

        const hideModal = () => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        document.querySelectorAll('[data-action="toggle-booking"]').forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.mode ?? 'pause';
                if (mode === 'resume' && closureField) {
                    closureField.value = button.dataset.closureId ?? '';
                }
                showModal(mode);
            });
        });

        modal.querySelectorAll('[data-close-booking]').forEach((button) => {
            button.addEventListener('click', hideModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                hideModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                hideModal();
            }
        });
    });
})();





