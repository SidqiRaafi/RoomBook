// Pass booking data from PHP (will be set inline in HTML)
// Expected: window.bookingsData = [...];

document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.getElementById('roomSelect');
    const startTimeSelect = document.getElementById('startTime');
    const endTimeSelect = document.getElementById('endTime');
    const dateInput = document.getElementById('bookingDate');
    const modal = document.getElementById('modalBooking');
    const btnOpenModal = document.getElementById('btnOpenModal');
    const bookingForm = document.getElementById('bookingForm');
    const modalAlert = document.getElementById('modalAlert');

    let currentBookingsData = window.bookingsData || [];

    // ✅ NEW: Fetch bookings for selected date
    async function fetchBookingsForDate(date) {
        try {
            const response = await fetch(`get_bookings_by_date.php?date=${date}`);
            const data = await response.json();
            currentBookingsData = data.bookings || [];
            updateTimeOptions();
        } catch (error) {
            console.error('Error fetching bookings:', error);
            currentBookingsData = [];
        }
    }

    // Function to get booked times for a specific room
    function getBookedTimes(roomId) {
        if (!currentBookingsData) return [];
        
        return currentBookingsData
            .filter(b => b.room_id == roomId)
            .map(b => ({
                start: b.start_time.substring(0, 5), // "HH:MM"
                end: b.end_time.substring(0, 5)
            }));
    }

    // Function to check if a time overlaps with bookings
    function isTimeBooked(time, roomId) {
        const bookedTimes = getBookedTimes(roomId);
        for (let booking of bookedTimes) {
            if (time >= booking.start && time < booking.end) {
                return true;
            }
        }
        return false;
    }

    // Update time dropdown options based on selected room
    function updateTimeOptions() {
        const roomId = roomSelect.value;
        
        if (!roomId) {
            resetTimeOptions();
            return;
        }

        // Update start time options
        Array.from(startTimeSelect.options).forEach((option, index) => {
            if (index === 0) return; // Skip placeholder
            const time = option.value;
            
            if (isTimeBooked(time, roomId)) {
                option.disabled = true;
                option.textContent = time + ' (Terisi)';
                option.style.color = '#9CA3AF';
            } else {
                option.disabled = false;
                option.textContent = time;
                option.style.color = '';
            }
        });

        // Update end time options
        Array.from(endTimeSelect.options).forEach((option, index) => {
            if (index === 0) return;
            const time = option.value;
            
            if (isTimeBooked(time, roomId)) {
                option.disabled = true;
                option.textContent = time + ' (Terisi)';
                option.style.color = '#9CA3AF';
            } else {
                option.disabled = false;
                option.textContent = time;
                option.style.color = '';
            }
        });
    }

    // Reset time options to default
    function resetTimeOptions() {
        [startTimeSelect, endTimeSelect].forEach(select => {
            Array.from(select.options).forEach((option, index) => {
                if (index === 0) return;
                option.disabled = false;
                option.textContent = option.value;
                option.style.color = '';
            });
        });
    }

    // Event listener for room selection
    if (roomSelect) {
        roomSelect.addEventListener('change', updateTimeOptions);
    }

    // ✅ NEW: Listen to date changes
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                fetchBookingsForDate(selectedDate);
            }
        });
    }

    // Modal Controls - Open
    if (btnOpenModal) {
        btnOpenModal.addEventListener('click', function() {
            modal.classList.add('active');
        });
    }

    // Modal Controls - Close when clicking outside
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    // Filter end time based on start time
    if (startTimeSelect) {
        startTimeSelect.addEventListener('change', function() {
            const selectedStart = this.value;
            const endOptions = endTimeSelect.querySelectorAll('option');
            
            endOptions.forEach(option => {
                if (option.value === '') return; // Skip placeholder
                
                if (option.value <= selectedStart) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });
            
            // Reset end time selection if it's invalid
            if (endTimeSelect.value && endTimeSelect.value <= selectedStart) {
                endTimeSelect.value = '';
            }
        });
    }

    // Handle form submission with AJAX
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('create_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalAlert.className = 'modal-alert success';
                    modalAlert.textContent = data.message;
                    modalAlert.style.display = 'block';
                    
                    // Reload page after 1.5 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    modalAlert.className = 'modal-alert error';
                    modalAlert.textContent = data.message;
                    modalAlert.style.display = 'block';
                }
            })
            .catch(error => {
                modalAlert.className = 'modal-alert error';
                modalAlert.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                modalAlert.style.display = 'block';
            });
        });
    }
});
