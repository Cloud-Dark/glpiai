# GLPI Plugin for OpenRouter 🤖

![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.2-8892BF.svg)

Welcome to the official repository for the OpenRouter plugin for GLPI! This plugin supercharges your IT support by integrating the power of Large Language Models (LLMs) from OpenRouter directly into your ticketing system.

## ✨ Features

-   **🧠 Automated Ticket Responses**: Automatically generate intelligent responses to user tickets and follow-ups.
-   **🚀 Dynamic Model Selection**: Choose from a wide range of models available on OpenRouter, fetched dynamically in the plugin's configuration.
-   **🔧 Customizable AI Behavior**: Tailor the AI's personality and instructions with a customizable system prompt.
-   **✅ Per-Ticket Control**: Easily disable the bot on specific tickets where automated responses are not needed.
-   **📈 Rate Limiting**: Set a daily limit on API calls to control costs and prevent abuse.
-   **🛡️ Loop Prevention**: A robust mechanism prevents the bot from replying to its own messages, avoiding infinite loops.
-   **📝 Error Logging**: The plugin logs errors to GLPI's standard log files for easy debugging.

## ⚙️ How It Works

The plugin uses a JavaScript trigger on the ticket page. When a user opens a ticket that doesn't have a bot response as the last entry, the plugin waits a few seconds and then makes an **asynchronous** AJAX call to a backend PHP script. This script fetches the ticket's last message, sends it to the OpenRouter API along with your configured system prompt and model, and then posts the AI's response as a new follow-up in the ticket. This asynchronous design ensures that the user interface remains responsive while the AI generates a response.

## 💾 Installation

1.  **Download**: Download the latest release from the [GitHub releases page](https://github.com/bricefourie/glpiai-openrouter/releases).
2.  **Extract**: Extract the archive into the `plugins` directory of your GLPI installation. The plugin will be in a directory named `openrouter`.
3.  **Install & Activate**: In GLPI, navigate to `Setup > Plugins`.
4.  Install and activate the "OpenRouter" plugin.

## 🛠️ Configuration

Before the plugin can work, you need to configure it. After installing and activating, go to the `Setup > General > OpenRouter` tab.

| Setting                    | Description                                                                                                |
| -------------------------- | ---------------------------------------------------------------------------------------------------------- |
| **OpenRouter API Key**     | Your secret API key from [OpenRouter](https://openrouter.ai/).                                              |
| **OpenRouter Model Name**  | The name of the model you want to use (e.g., `google/gemini-flash-1.5`). The list is loaded dynamically.       |
| **OpenRouter System Prompt** | A custom prompt to define the AI's role and behavior. If left empty, a default prompt will be used.        |
| **Bot User ID**            | The GLPI user ID for the bot that will post the responses.                                                 |
| **Max API Usage Per Day**  | The maximum number of times the API can be called in a 24-hour period.                                     |
| **API Usage Reset Time**   | The time of day when the API usage counter resets.                                                         |
| **Current API Usage Count**| A read-only counter showing the number of API calls made in the current period.                            |

> **Note**: The plugin will not work until the API key, model name, and bot user ID are provided.

## 🗺️ Roadmap

### Done
-   [x] Basic plugin structure following GLPI guidelines.
-   [x] Configuration page for API key, model name, and bot user ID.
-   [x] Integration with OpenRouter to respond to tickets and followups.
-   [x] Robust loop prevention mechanism to avoid bot-to-bot conversations.
-   [x] Error logging to GLPI's log files for easier debugging.
-   [x] Professional and detailed system prompt for the LLM.
-   [x] GitHub Actions workflow for automated building of the plugin archive.
-   [x] Check for required configuration before activating the plugin.

---

Made with ❤️ by [Brice Fourie](https://github.com/bricefourie)
