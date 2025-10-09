window.addEventListener('load', function () {
  if (!location.pathname.includes('/dev/taskticket')) {
    console.log('[developer.js] loaded but not on /dev/taskticket');
    return;
  }

  console.log('[developer.js] running...');

  const ticketList = document.getElementById('ticket-list');
  if (ticketList && ticketList.scrollHeight > ticketList.clientHeight) {
    setTimeout(() => {
      ticketList.scrollTop = ticketList.scrollHeight;
    }, 200);
  }
});
