# GLPI Plugin for AI Integration 🤖

![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.2-8892BF.svg)

Welcome to the official repository for the AI Assistant plugin for GLPI! This plugin supercharges your IT support by integrating the power of Large Language Models (LLMs) from multiple providers directly into your ticketing system. The plugin currently supports OpenRouter, Ollama, and Google Gemini, with the ability to easily switch between providers based on your needs and preferences.

## ✨ Features

-   **🧠 Automated Ticket Responses**: Automatically generate intelligent responses to user tickets and follow-ups.
-   **🚀 Multi-Provider Support**: Choose from multiple AI providers including OpenRouter, Ollama, and Google Gemini with dynamic model selection.
-   **🔧 Customizable AI Behavior**: Tailor the AI's personality and instructions with a customizable system prompt that works across all providers.
-   **✅ Per-Ticket Control**: Easily disable the bot on specific tickets where automated responses are not needed.
-   **📈 Rate Limiting**: Set a daily limit on API calls to control costs and prevent abuse across all providers.
-   **🛡️ Loop Prevention**: A robust mechanism prevents the bot from replying to its own messages, avoiding infinite loops.
-   **📝 Error Logging**: The plugin logs errors to GLPI's standard log files for easy debugging.
-   **🌐 Self-Hosted & Cloud Support**: Support for both cloud-based providers (OpenRouter, Gemini) and self-hosted solutions (Ollama).

## ⚙️ How It Works

The plugin uses a JavaScript trigger on the ticket page. When a user opens a ticket that doesn't have a bot response as the last entry, the plugin waits a few seconds and then makes an **asynchronous** AJAX call to a backend PHP script. This script fetches the ticket's last message, sends it to the selected AI provider along with your configured system prompt and model, and then posts the AI's response as a new follow-up in the ticket. This asynchronous design ensures that the user interface remains responsive while the AI generates a response.

## 💾 Installation

1.  **Download**: Download the latest release from the [GitHub releases page](https://github.com/Cloud-Dark/glpiai/releases).
2.  **Extract**: Extract the archive into the `plugins` directory of your GLPI installation. The plugin will be in a directory named `openrouter` (despite the name, it supports all providers).
3.  **Install & Activate**: In GLPI, navigate to `Setup > Plugins`.
4.  Install and activate the "AI Assistant" plugin (appears under the 'openrouter' name in GLPI).

## 🛠️ Configuration

Before the plugin can work, you need to configure it. After installing and activating, go to the `Setup > General > OpenRouter` tab (the tab name remains for historical reasons).

| Setting                    | Description                                                                                                |
| -------------------------- | ---------------------------------------------------------------------------------------------------------- |
| **AI Provider**            | Select which AI provider to use: OpenRouter, Ollama, or Google Gemini.                                     |
| **API Keys**               | Provider-specific API keys (required for OpenRouter and Gemini, optional for Ollama).                       |
| **Model Name**             | The name of the model to use from your selected provider. The list is loaded dynamically based on provider. |
| **System Prompt**          | A custom prompt to define the AI's role and behavior across all providers. If left empty, a default prompt will be used. |
| **Bot User ID**            | The GLPI user ID for the bot that will post the responses (used by all providers).                         |
| **Max API Usage Per Day**  | The maximum number of times the API can be called in a 24-hour period across all providers.                |
| **API Usage Reset Time**   | The time of day when the API usage counter resets.                                                         |
| **Current API Usage Count**| A read-only counter showing the number of API calls made in the current period across all providers.       |

> **Note**: The plugin will not work until the required fields for your selected provider are provided. Configuration requirements vary by provider:
> - **OpenRouter**: API key, model name, and bot user ID required
> - **Ollama**: Model name and bot user ID required (API key optional), plus the Ollama API URL
> - **Google Gemini**: API key, model name, and bot user ID required

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
-   [x] Support for Ollama self-hosted LLMs.
-   [x] Support for Google Gemini API.
-   [x] Multi-provider architecture allowing seamless switching between AI providers.
-   [x] Dynamic model loading for all supported providers.

---

Made with ❤️ by [Cloud-Dark](https://github.com/Cloud-Dark)
