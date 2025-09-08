const seatsGrid = document.getElementById('seatsGrid');
const selectedSeatsSpan = document.getElementById('selectedSeats');
const totalAmountSpan = document.getElementById('totalAmount');
const clearBtn = document.getElementById('clearSelection');
const confirmBtn = document.getElementById('confirmBooking');
const seatPrice = 2000;

let selectedSeats = new Set();

// Create seat buttons
const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
const seatsPerRow = 5;

function createSeats() {
  seatsGrid.innerHTML = ""; // clear before re-creating
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
  if (seatBtn.disabled) return; // prevent clicking booked seats
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
    if (!btn.disabled) { // don't reset booked seats
      btn.style.backgroundColor = '';
      btn.style.color = '';
    }
  });
  updateBookingInfo();
}

async function confirmBooking() {
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

  const seats = [...selectedSeats].join(", ");
  const total = selectedSeats.size * seatPrice;

  try {
    const response = await fetch("save_booking.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `showDate=${encodeURIComponent(date)}&showTime=${encodeURIComponent(time)}&seats=${encodeURIComponent(seats)}&totalAmount=${total}`
    });

    const result = await response.text();

    // Save booking info locally
    localStorage.setItem('bookedSeats', JSON.stringify([...selectedSeats]));
    localStorage.setItem('totalAmount', total);
    localStorage.setItem('showDate', date);
    localStorage.setItem('showTime', time);

    if (result.trim() === "success") {
      alert(`Booking Confirmed!\nDate: ${date}\nTime: ${time}\nSeats: ${[...selectedSeats].join(', ')}\nTotal: LKR. ${total}`);
      window.location.href = 'payment.html';
    } else {
      alert("Booking failed: " + result);
    }
  } catch (error) {
    alert("Error connecting to server: " + error);
  }
}

// 🔹 Fetch booked seats from DB
async function fetchBookedSeats() {
  const date = document.getElementById('showDate').value;
  const time = document.getElementById('showtimeSelect').value;

  if (!date || !time) return;

  try {
    const response = await fetch("get_booked_seats.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `showDate=${encodeURIComponent(date)}&showTime=${encodeURIComponent(time)}`
    });

    const bookedSeats = await response.json();

    document.querySelectorAll('.seat').forEach(btn => {
      if (bookedSeats.includes(btn.textContent)) {
        btn.disabled = true;
        btn.style.backgroundColor = "red";
        btn.style.color = "white";
      } else {
        btn.disabled = false;
        btn.style.backgroundColor = "";
        btn.style.color = "";
      }
    });

  } catch (error) {
    console.error("Error fetching booked seats:", error);
  }
}

// Event listeners
clearBtn.addEventListener('click', clearSelection);
confirmBtn.addEventListener('click', confirmBooking);
document.getElementById('showDate').addEventListener('change', fetchBookedSeats);
document.getElementById('showtimeSelect').addEventListener('change', fetchBookedSeats);

// Init
createSeats();
updateBookingInfo();
