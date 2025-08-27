# GLPI Plugin for OpenRouter

This GLPI plugin allows you to use a Large Language Model (LLM) from OpenRouter to automatically respond to user tickets.

## Description

The plugin integrates with the OpenRouter API to provide intelligent, automated responses to user-submitted tickets. Administrators can configure the plugin with their OpenRouter API key, choose a specific model to use for generating responses, and set a dedicated user ID for the bot.

When a new ticket or followup is created by a user, the plugin sends the content to the selected LLM, along with a carefully crafted system prompt that instructs the model to act as a Level 1 IT support technician. The LLM's response is then added as a public followup to the ticket, helping to resolve common issues quickly and efficiently.

## Installation

1.  Download the latest release from the [GitHub releases page](https://github.com/your-repo/your-plugin/releases).
2.  Extract the archive into the `plugins` directory of your GLPI installation. The plugin will be in a directory named `openrouter`.
3.  In GLPI, navigate to `Setup > Plugins`.
4.  Install and activate the "OpenRouter" plugin.
5.  Configure the plugin by going to the "OpenRouter" tab in the configuration section. You will need to provide:
    *   Your OpenRouter API key.
    *   The name of the model you want to use (e.g., `google/gemini-flash-1.5`).
    *   The GLPI user ID for the bot that will post the responses.

## Roadmap

### Done
-   [x] Basic plugin structure following GLPI guidelines.
-   [x] Configuration page for API key, model name, and bot user ID.
-   [x] Integration with OpenRouter to respond to tickets and followups.
-   [x] Robust loop prevention mechanism to avoid bot-to-bot conversations.
-   [x] Error logging to GLPI's log files for easier debugging.
-   [x] Professional and detailed system prompt for the LLM.
-   [x] GitHub Actions workflow for automated building of the plugin archive.
-   [x] Check for required configuration before activating the plugin.

### To Do
-   [ ] Add comprehensive unit tests.
-   [ ] Implement more advanced loop prevention logic (e.g., based on ticket status).
-   [ ] Allow administrators to customize the system prompt from the UI.
-   [ ] Add support for other LLM providers besides OpenRouter.
-   [ ] Provide more detailed and structured logging.
-   [ ] Add a feature to temporarily disable the bot for a specific ticket.
