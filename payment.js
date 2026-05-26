const paymentForm = document.getElementById('paymentForm');
const leftPanel = document.getElementById('leftPanel');
const rightImage = document.getElementById('rightImage');
const successMessage = document.getElementById('successMessage');
const payBtn = document.getElementById('payBtn');

// === FORM VALIDATION + PAYMENT ===
paymentForm.addEventListener('submit', async function (e) {
  e.preventDefault();

  const cardName = document.getElementById('cardName').value.trim();
  const cardNumber = document.getElementById('cardNumber').value.trim().replace(/\s+/g, '');
  const expiryDate = document.getElementById('expiryDate').value;
  const cvv = document.getElementById('cvv').value.trim();
  const bookingId = localStorage.getItem('bookingId');

  if (!bookingId) {
    alert('No active booking session found. Please return to the booking page.');
    window.location.href = 'booking.php';
    return;
  }

  if (!cardName || !cardNumber || !expiryDate || !cvv) {
    alert('Please fill in all the fields.');
    return;
  }
  if (!/^\d{16}$/.test(cardNumber)) {
    alert('Card number must be 16 digits.');
    return;
  }
  if (!/^\d{3,4}$/.test(cvv)) {
    alert('CVV must be 3 or 4 digits.');
    return;
  }

  payBtn.disabled = true;
  payBtn.textContent = 'Processing...';

  try {
    const formData = new FormData();
    formData.append('bookingId', bookingId);
    formData.append('cardName', cardName);
    formData.append('cardNumber', cardNumber);
    formData.append('expiryDate', expiryDate);
    formData.append('cvv', cvv);

    const response = await fetch('process_payment.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (data.success) {
      leftPanel.style.display = 'none';
      rightImage.style.display = 'none';
      successMessage.style.display = 'block';

      // Retrieve booking info from localStorage
      const bookedSeats = JSON.parse(localStorage.getItem('bookedSeats')) || [];
      const totalAmount = localStorage.getItem('totalAmount') || 0;
      const showDate = localStorage.getItem('showDate') || "N/A";
      const showTime = localStorage.getItem('showTime') || "N/A";

      setTimeout(() => {
        openTicketModal({
          name: cardName,
          event: "Movie Night",
          date: showDate,
          time: showTime,
          seats: bookedSeats,
          amount: totalAmount,
          ticketId: "TCK" + bookingId.toString().padStart(6, '0')
        });
      }, 1500);
    } else {
      alert("Payment failed: " + data.message);
      payBtn.disabled = false;
      payBtn.textContent = 'Pay Now';
    }
  } catch (error) {
    console.error("Error processing payment:", error);
    alert("Connection error. Please try again.");
    payBtn.disabled = false;
    payBtn.textContent = 'Pay Now';
  }
});

// === TICKET MODAL FUNCTIONALITY ===
function openTicketModal(ticket) {
  const modal = document.createElement("div");
  modal.classList.add("ticket-modal");

  modal.innerHTML = `
<div class="ticket-card">
  <div class="ticket-left">
    <h3>${ticket.event} Ticket</h3>
    <p><strong>Name:</strong> ${ticket.name}</p>
    <p><strong>Date:</strong> ${ticket.date}</p>
    <p><strong>Time:</strong> ${ticket.time}</p>
    <p><strong>Seats:</strong> ${ticket.seats.join(', ')}</p>
    <p><strong>Amount:</strong> LKR. ${ticket.amount}</p>
    <div class="ticket-actions">
      <button id="printTicket">Print</button>
      <button id="downloadTicket">Download</button>
    </div>
  </div>
  <div class="ticket-right">
    <div id="qrcode"></div>
    <p class="qr-note">Scan for entry</p>
  </div>
</div>
<button class="close-ticket">Close</button>
`;

  document.body.appendChild(modal);

  if (typeof QRCode !== "undefined") {
    new QRCode(modal.querySelector("#qrcode"), {
      text: ticket.ticketId,
      width: 120,
      height: 120
    });
  }

  modal.querySelector("#printTicket").addEventListener("click", () => window.print());

  modal.querySelector("#downloadTicket").addEventListener("click", async () => {
    if (typeof html2canvas !== "undefined") {
      const canvas = await html2canvas(modal.querySelector(".ticket-card"));
      const link = document.createElement("a");
      link.download = `${ticket.ticketId}.png`;
      link.href = canvas.toDataURL();
      link.click();
    } else {
      alert("Download feature requires html2canvas.js");
    }
  });

  modal.querySelector(".close-ticket").addEventListener("click", () => modal.remove());
}
