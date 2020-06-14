# Inclusive Teamwork Self Study Toolkit

This guide is for getting started as a developer on this project, not for installing and using the app.

## Setup - Local

Prerequisites:

- [Homestead VM](https://laravel.com/docs/7.x/homestead)
- with [ngrok](https://ngrok.com/) or another tunnel option installed

### Install

- Clone the repo into a new project directory on your local machine: `git clone git@github.com:mstaples/self-study-toolkit.git`
- Add the new project to the `Homestead.yaml` `folders:` and `sites:` section.

```yaml
folders:
    - map: ~/php/self-study-toolkit
      to: /home/vagrant/self-study-toolkit
sites:
    - map: localdev.test
      to: /home/vagrant/self-study-toolkit/public
```

- Connect to your VM using `vagrant ssh`
- Change to the project directory (`cd /home/vagrant/self-study-toolkit`)
- Copy the `.env.example` to a new `.env` file, and update the relevant local values
- Run `composer install` to install all dependencies
- Run `php artisan migrate:fresh --seed` to create your database

## Setup - Slack

To test Slack API interactions which require the API to reach a callback, you will need to setup your own test Slack app.

Go to the Slack app developer page and use the "Create New App" button.

### App config - Basic Information

From the Basic Information tab, select the following under "Add features and functionality"

- Incoming Webhooks
- Interactive Components
- Event Subscriptions
- Bots
- Permissions

Create a new Slack app to test your development, and link that under "Install your app to your workspace"

### App config - App Home

From the App Home tab, under "Your App’s Presence in Slack", add the following name:

```text
Display Name (Bot Name): helper_bot
Default Name: helper_bot
```

Under "Show Tabs" toggle the sliders to enable "Home Tab" and "Messages Tab"

### App config - Incoming Webhooks

From the Incoming Webhooks tab, toggle the slider to enable "Activate Incoming Webhooks"

Under "Webhook URLs for Your Workspace" click the button to "Add New Webhook to Workspace" and select a channel in your test Slack for the app to post to when using the new webhook.

This will generate a curl sample you can use to test posting to your test channel.

### App config - Interactivity & Shortcuts

From the Interactivity & Shortcuts tab, toggle the slider to enable "Interactivity".

The URL you provide as "Request URL" is where the Slack App will send data from user interactions. The path beyond your base URL should be "/api/slack/action"

If you don't have an ngrok subdomain you will need to update this with the generated URL each time you need to restart ngrok.

Under "Select Menus" add your base URL again with the path "/api/slack/menus".

### App config - OAuth & Permissions

From the OAuth & Permissions tab, scroll down to the "Scopes" section and add any needed scopes to your bot.

### App config - Event Subscriptions

From the Event Subscriptions tab, toggle the slider to "Enable Events" and add your base URL with the path "/api/slack/events".

Under "Subscribe to events on behalf of users" and click the "Add Workspace Event" button and then select "app_home_opened" as the event to subscribe to.

## Contributing

Please, do all development in a separate branch named for a specific development goal and [submit a Pull Request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request) when you're ready for your work to be integrated.

Be sure to pull down and integrate changes from the master branch before making new commits.

### TODO

- Initiate sampling questions evaluation on initial opt-in action (app/Http/Traits/SlackApiTrait: firstSampling()).
- Evaluating and storing user responses (app/Http/Traits/SlackApiTrait: parseActions(Operator $operator, $slackActions)).
- Available Paths menu based on Sampling evaluation
- Prompt delivery
- Feedback request delivery
- Feedback record
