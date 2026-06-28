# Telegram 插件

XBoard provides user account binding Telegram Bot traffic query，subscription link acquisition, and other functions.、## Function Features、Ticket notification function。

Configurable switch

-   ✅ Payment notification function（Configurable switch）
-   ✅ User account binding（Unbinding）
-   ✅ Traffic usage inquiry/Subscription link acquisition
-   ✅ Support for ticket replies
-   ✅ ## Available Commands
-   ✅ ### `/start` - Start using

Welcome new users and display help information

Supports dynamic configuration

### `/bind` - Bind account，Bind the user's。

account to a

subscription link XBoard ### `/traffic` - View traffic Telegram。

```
/bind [订阅链接]
```

### `/traffic` - 查看流量

Check the current account's traffic usage。

### `/getlatesturl` - Get Subscription Link

Get the latest subscription link。

### `/unbind` - Unbind Account

Unbind the current Telegram account from XBoard another account。

## Configuration Options

### Basic Configuration

| Configuration Item       | Type    | Default Value                                                                                     | Description                 |
| ------------ | ------- | ------------------------------------------------------------------------------------------ | -------------------- |
| `auto_reply` | boolean | true                                                                                       | Whether to automatically reply to unknown commands |
| `help_text`  | text    | 'Please use the following command: \\n/bind - Bind Account\\n/traffic - Check Traffic\\n/getlatesturl - Get Latest Link' | 未知命令的回复文本   |

### Dynamic Configuration for `/start` Command

| Configuration Item                  | Type | Description                     |
| ----------------------- | ---- | ------------------------ |
| `start_welcome_title`   | text | Welcome Title                 |
| `start_bot_description` | text | Introduction to Bot Functions           |
| `start_bind_guide`      | text | Guidance for users who are not bound     |
| `start_unbind_guide`    | text | List of commands displayed to bound users |
| `start_bind_commands`   | text | List of commands displayed to unbound users |
| `start_footer`          | text | Bottom Prompt Information             |

### Work Order Notification Configuration

| Configuration Item                 | Type    | Default Value | Description                 |
| ---------------------- | ------- | ------ | -------------------- |
| `enable_ticket_notify` | boolean | true   | Whether to enable work order notification function |

### Payment Notification Configuration

| Configuration Item                  | Type    | Default Value | Description                 |
| ----------------------- | ------- | ------ | -------------------- |
| `enable_payment_notify` | boolean | true   | Whether to enable payment notification function |

## Usage Process

### New User Usage Process

1. First-time user usage Bot，Send `/start`
2. Bind account according to the prompt：`/bind [Subscription link]`
3. After binding successfully, other functions can be used

### Daily Usage Process

1. Check traffic：`/traffic`
2. Get subscription link：`/getlatesturl`
3. Manage bindings：`/unbind`
