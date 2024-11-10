(function (Drupal, drupalSettings) {
  Drupal.behaviors.clockWidgetScripts = {
    attach: function (context, settings) {
      // Ensure the initial time settings are available
      if (!drupalSettings.clock_widget || !drupalSettings.clock_widget.initial_time_est || !drupalSettings.clock_widget.initial_time_utc) {
        console.error("Clock widget initial time settings are missing.");
        return;
      }

      // Get initial time from drupalSettings
      let estTime = new Date(drupalSettings.clock_widget.initial_time_est);
      let utcTime = new Date(drupalSettings.clock_widget.initial_time_utc);

      // Check if there is a saved time in localStorage
      const savedEstTime = localStorage.getItem('savedEstTime');
      const savedUtcTime = localStorage.getItem('savedUtcTime');
      
      if (savedEstTime && savedUtcTime) {
        // If there's a saved time, use that
        estTime = new Date(parseInt(savedEstTime));
        utcTime = new Date(parseInt(savedUtcTime));
      } else {
        // Otherwise, use the initial time and store it in localStorage
        localStorage.setItem('savedEstTime', estTime.getTime());
        localStorage.setItem('savedUtcTime', utcTime.getTime());
      }

      // Get clock elements
      const estElement = document.getElementById('clock-est');
      const utcElement = document.getElementById('clock-utc');

      // Function to update the clocks
      function updateClock() {
        estElement.querySelector('.time').textContent = estTime.toLocaleTimeString('en-US', { timeZone: 'America/New_York' });
        utcElement.querySelector('.time').textContent = utcTime.toLocaleTimeString('en-US', { timeZone: 'UTC' });
      }

      // Update time every second
      setInterval(function () {
        // Increment time by one second
        estTime.setSeconds(estTime.getSeconds() + 1);
        utcTime.setSeconds(utcTime.getSeconds() + 1);

        // Update clocks with the new time
        updateClock();

        // Save the new times to localStorage to persist across reloads
        localStorage.setItem('savedEstTime', estTime.getTime());
        localStorage.setItem('savedUtcTime', utcTime.getTime());
      }, 1000);

      // Sync time with server every 10 seconds to ensure the time remains accurate
      setInterval(function () {
        // Fetch updated time from drupalSettings (or API if needed)
        const updatedEstTime = new Date(drupalSettings.clock_widget.initial_time_est);
        const updatedUtcTime = new Date(drupalSettings.clock_widget.initial_time_utc);

        // Update the clocks with the new time from server
        estTime = updatedEstTime;
        utcTime = updatedUtcTime;

        // Update the clocks with the new time
        updateClock();
      }, 10000);  // Sync every 10 seconds (or adjust based on your needs)

      // Initialize clocks immediately with the starting time
      updateClock();
    }
  };
})(Drupal, drupalSettings);
