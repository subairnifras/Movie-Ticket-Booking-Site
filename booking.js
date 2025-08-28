const seatsGrid = document.getElementById('seatsGrid');
const selectedSeatsSpan = document.getElementById('selectedSeats');
const totalAmountSpan = document.getElementById('totalAmount');
const clearBtn = document.getElementById('clearSelection');
const confirmBtn = document.getElementById('confirmBooking');
const seatPrice = 2000; // Example price per seat in LKR

let selectedSeats = new Set();

// Create seat buttons
const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
const seatsPerRow = 5;

function createSeats() {
  for (let r = 0; r < rows.length; r++) {
    for (let s = 1; s <= seatsPerRow; s++) {
      const seatBtn = document.createElement('button');
      seatBtn.textContent = rows[r] + s;
      seatBtn.className = 'seat';
      seatBtn.addEventListener('click', () => toggleSeat(seatBtn));
      seatsGrid.appendChild(seatBtn);
    }
  }
}

function toggleSeat(seatBtn) {
  const seat = seatBtn.textContent;
  if (selectedSeats.has(seat)) {
    selectedSeats.delete(seat);
    seatBtn.style.backgroundColor = '';
    seatBtn.style.color = '';
  } else {
    selectedSeats.add(seat);
    seatBtn.style.backgroundColor = 'green';
    seatBtn.style.color = 'white';
  }
  updateBookingInfo();
}

function updateBookingInfo() {
  selectedSeatsSpan.textContent = selectedSeats.size;
  totalAmountSpan.textContent = selectedSeats.size * seatPrice;
}

function clearSelection() {
  selectedSeats.clear();
  document.querySelectorAll('.seat').forEach(btn => {
    btn.style.backgroundColor = '';
    btn.style.color = '';
  });
  updateBookingInfo();
}

function confirmBooking() {
  if (selectedSeats.size === 0) {
    alert('Please select at least one seat before confirming.');
    return;
  }
  const date = document.getElementById('showDate').value;
  const time = document.getElementById('showtimeSelect').value;
  if (!date) {
    alert('Please select a show date.');
    return;
  }
  if (!time) {
    alert('Please select a show time.');
    return;
  }

  // Save booking info to localStorage
  localStorage.setItem('bookedSeats', JSON.stringify([...selectedSeats]));
  localStorage.setItem('totalAmount', selectedSeats.size * seatPrice);
  localStorage.setItem('showDate', date);
  localStorage.setItem('showTime', time);

  alert(`Booking Confirmed!\nDate: ${date}\nTime: ${time}\nSeats: ${[...selectedSeats].join(', ')}\nTotal: LKR. ${selectedSeats.size * seatPrice}`);
  
  // Redirect to payment page
  window.location.href = 'payment.html';
}

clearBtn.addEventListener('click', clearSelection);
confirmBtn.addEventListener('click', confirmBooking);

createSeats();
updateBookingInfo();
