// Get booked times for a specific room (excluding current booking being edited)
function getBookedTimesForEdit(roomId, bookingDate, currentBookingId) {
    if (!window.bookingsData) return [];
    
    return window.bookingsData
        .filter(b => b.room_id == roomId && b.booking_date == bookingDate && b.id != currentBookingId)
        .map(b => ({
            start: b.start_time.substring(0, 5),
            end: b.end_time.substring(0, 5)
        }));
}

// Check if a time overlaps with bookings
function isTimeBookedForEdit(time, roomId, bookingDate, currentBookingId) {
    const bookedTimes = getBookedTimesForEdit(roomId, bookingDate, currentBookingId);
    for (let booking of bookedTimes) {
        if (time >= booking.start && time < booking.end) {
            return true;
        }
    }
    return false;
}

// Update time dropdown options to show booked times
function updateEditTimeOptions(roomId, bookingDate, currentBookingId) {
    const startTimeSelect = document.getElementById('edit_start_time');
    const endTimeSelect = document.getElementById('edit_end_time');
    
    if (!roomId || !bookingDate) return;
    
    // Update start time options
    Array.from(startTimeSelect.options).forEach((option, index) => {
        if (index === 0) return; // Skip placeholder
        const time = option.value;
        
        if (isTimeBookedForEdit(time, roomId, bookingDate, currentBookingId)) {
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
        
        if (isTimeBookedForEdit(time, roomId, bookingDate, currentBookingId)) {
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

// Open Edit Modal
function openEditModal(booking) {
    const modal = document.getElementById('editModal');
    
    // ✅ FIXED: Fill form with booking data including date
    document.getElementById('edit_booking_id').value = booking.id;
    document.getElementById('edit_booking_date').value = booking.booking_date;
    document.getElementById('edit_room_id').value = booking.room_id;
    document.getElementById('edit_start_time').value = booking.start_time.substring(0, 5);
    document.getElementById('edit_end_time').value = booking.end_time.substring(0, 5);
    document.getElementById('edit_purpose').value = booking.purpose;
    
    // Update time options to show booked times
    updateEditTimeOptions(booking.room_id, booking.booking_date, booking.id);
    
    modal.classList.add('active');
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('active');
    }
});

// Handle Edit Form Submit
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const alert = document.getElementById('editAlert');
    
    fetch('update_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert.className = 'modal-alert success';
            alert.textContent = data.message;
            alert.style.display = 'block';
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert.className = 'modal-alert error';
            alert.textContent = data.message;
            alert.style.display = 'block';
        }
    })
    .catch(error => {
        alert.className = 'modal-alert error';
        alert.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
        alert.style.display = 'block';
    });
});

// Delete Booking
function deleteBooking() {
    const bookingId = document.getElementById('edit_booking_id').value;
    
    if (!confirm('Yakin ingin menghapus booking ini?')) {
        return;
    }
    
    fetch('delete_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ booking_id: bookingId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking berhasil dihapus!');
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Print Booking
function printBooking(bookingId) {
    window.open('print_booking.php?id=' + bookingId, '_blank');
}

// Listen to changes in edit modal
document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.getElementById('edit_room_id');
    const dateInput = document.getElementById('edit_booking_date');
    
    // Update when room changes
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            const currentBookingId = document.getElementById('edit_booking_id').value;
            const bookingDate = document.getElementById('edit_booking_date').value;
            updateEditTimeOptions(this.value, bookingDate, currentBookingId);
        });
    }
    
    // ✅ NEW: Update when date changes
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            const currentBookingId = document.getElementById('edit_booking_id').value;
            const roomId = document.getElementById('edit_room_id').value;
            updateEditTimeOptions(roomId, this.value, currentBookingId);
        });
    }
});
