document.addEventListener('DOMContentLoaded', function() {
    // Check if we are on a ticket page
    const form = document.querySelector('form[name="form_ticket"]');
    if (!form) {
        return;
    }

    const ticketId = form.querySelector('input[name="id"]').value;
    if (!ticketId) {
        return;
    }

    // Check if the last response was from the bot
    const timeline = document.querySelector('.timeline-container');
    if (timeline) {
        const lastMessage = timeline.querySelector('.timeline-item:last-child');
        if (lastMessage && lastMessage.innerHTML.includes('<!-- openrouter_bot_response -->')) {
            // Last message is from the bot, do nothing.
            return;
        }
    }

    // If we are here, it means we need to trigger the bot.
    // We'll add a small delay to ensure the page is fully loaded and not to be too intrusive.
    setTimeout(() => {
        const formData = new FormData();
        formData.append('ticket_id', ticketId);

        fetch('../plugins/openrouter/ajax/create_followup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the new followup
                location.reload();
            } else {
                console.error('Error creating followup:', data.error);
            }
        })
        .catch(error => {
            console.error('Error creating followup:', error);
        });
    }, 1000);
});
