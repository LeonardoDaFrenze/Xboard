# Xboard Automated Test Plan & Guide

This document serves as a comprehensive guide and checklist for the automated test suite in **Xboard**. Use it as a reference before performing refactors, implementing features, or adding new test coverage.

---

## 📖 Guide: Writing Tests in Xboard

To ensure the integrity of the test suite and database isolation, please adhere to these conventions:

### 1. Database Parity & Environment
* **Database Driver:** Tests run using an in-memory SQLite database configuration.
* **Schema Integrity:** Always use the `Illuminate\Foundation\Testing\RefreshDatabase` trait in your test cases. This ensures migrations run before tests and transactions roll back afterward.
* **Factories:** Use Laravel factories to seed mock data. Ensure all custom models include the `HasFactory` trait. Avoid hardcoding database attributes that might conflict with actual schema requirements (e.g., nullable constraints, foreign keys).

### 2. Feature (API) Tests
* **Namespace:** `Tests\Feature` (stored in `tests/Feature/`)
* **Admin Routes:** Always use the dynamic `$this->getAdminUri('endpoint')` helper instead of hardcoding routes like `/api/v1/admin/` or `/api/v2/admin/`. This is critical as security prefixes vary.
* **Authentication:** Simulate a user session using `actingAs()`:
  ```php
  // For User API
  $user = User::factory()->create();
  $response = $this->actingAs($user)->getJson('/api/v1/user/info');

  // For Admin API
  $admin = User::factory()->create(['is_admin' => 1]);
  $response = $this->actingAs($admin)->postJson($this->getAdminUri('coupon/show'), ['id' => $coupon->id]);
  ```

### 3. Unit & Protocol Tests
* **Namespace:** `Tests\Unit` (stored in `tests/Unit/`)
* **Avoid Shallow Tests:** Do not write tests that only verify class or method existence (e.g., `assertTrue(class_exists(...))`). 
* **Stateful Assertions:** Ensure protocol generators (like Clash, SingBox, Shadowrocket) are supplied with model data (User, Server) and assert that the generated config structure matches expectations.
  ```php
  public function test_clash_config_generation()
  {
      $user = User::factory()->create();
      $server = Server::factory()->create(['type' => 'shadowsocks']);
      
      $clash = new Clash($user, $server);
      $config = $clash->handle();

      $this->assertArrayHasKey('proxies', $config);
      $this->assertEquals('shadowsocks', $config['proxies'][0]['type']);
  }
  ```

---

## 📋 Comprehensive Checklist

### 1. Models & Unit Tests
Verifies database interactions, factories, custom scopes, attributes, and model relationships.

* [x] `AdminAuditLog` (`AdminAuditLogTest.php`)
* [x] `CommissionLog` (`CommissionLogTest.php`)
* [x] `Coupon` (`CouponTest.php`)
* [x] `GiftCardCode` (`GiftCardCodeTest.php`)
* [x] `GiftCardTemplate` (`GiftCardTemplateTest.php`)
* [x] `GiftCardUsage` (`GiftCardUsageTest.php`)
* [x] `InviteCode` (`InviteCodeTest.php`)
* [x] `Knowledge` (`KnowledgeTest.php`)
* [x] `MailLog` (`MailLogTest.php`)
* [x] `MailTemplate` (`MailTemplateTest.php`)
* [x] `Notice` (`NoticeTest.php`)
* [x] `Order` (`OrderTest.php`)
* [x] `Payment` (`PaymentTest.php`)
* [x] `Plan` (`PlanTest.php`)
* [x] `Plugin` (`PluginTest.php`)
* [x] `ServerGroup` (`ServerGroupTest.php`)
* [x] `ServerMachine` (`ServerMachineTest.php`)
* [x] `ServerMachineLoadHistory` (`ServerMachineLoadHistoryTest.php`)
* [x] `ServerRoute` (`ServerRouteTest.php`)
* [x] `Server` (`ServerTest.php`)
* [x] `ServerStat` (`ServerStatTest.php`)
* [x] `Setting` (`SettingTest.php`)
* [x] `Stat` (`StatTest.php`)
* [x] `StatServer` (`StatServerTest.php`)
* [x] `StatUser` (`StatUserTest.php`)
* [x] `SubscribeTemplate` (`SubscribeTemplateTest.php`)
* [x] `Ticket` (`TicketTest.php`)
* [x] `TicketMessage` (`TicketMessageTest.php`)
* [x] `TrafficResetLog` (`TrafficResetLogTest.php`)
* [x] `User` (`UserTest.php`)

---

### 2. Services & Unit Tests
Verifies business logic processing, external tool setups, and helper service logic.

* [x] `Auth\LoginService` (Fully functional test)
* [x] `Auth\RegisterService` (Fully functional test)
* [x] `Auth\MailLinkService` (`MailLinkServiceTest.php`)
* [x] `AuthService` (`AuthServiceTest.php`)
* [x] `CaptchaService` (`CaptchaServiceTest.php`)
* [x] `CouponService` (`CouponServiceTest.php`)
* [x] `DeviceStateService` (`DeviceStateServiceTest.php`)
* [x] `GiftCardService` (`GiftCardServiceTest.php`)
* [x] `MailService` (`MailServiceTest.php`)
* [x] `NodeRegistry` (`NodeRegistryTest.php`)
* [x] `NodeSyncService` (`NodeSyncServiceTest.php`)
* [x] `OrderService` (`OrderServiceTest.php`)
* [x] `PaymentService` (`PaymentServiceTest.php`)
* [x] `PlanService` (`PlanServiceTest.php`)
* [x] `Plugin\PluginManager` (Instantiability and loading)
* [x] `Plugin\PluginConfigService` (`PluginConfigServiceTest.php`)
* [x] `Plugin\HookManager` (`HookManagerTest.php`)
* [x] `ServerService` (`ServerServiceTest.php`)
* [x] `SettingService` (`SettingServiceTest.php`)
* [x] `StatisticalService` (`StatisticalServiceTest.php`)
* [x] `TelegramService` (`TelegramServiceTest.php`)
* [x] `TrafficResetService` (Fully functional test)
* [x] `ThemeService` (`ThemeServiceTest.php`)
* [x] `TicketService` (`TicketServiceTest.php`)
* [x] `UpdateService` (`UpdateServiceTest.php`)
* [x] `UserService` (`UserServiceTest.php`)

---

### 3. Subscription Protocols & Serialization
Asserts configuration generation output structure, serialization, and content headers.

* [x] `Clash` (`ClashProtocolTest.php`)
* [x] `ClashMeta` (`ClashMetaProtocolTest.php`)
* [x] `General` (`GeneralProtocolTest.php`)
* [x] `Loon` (`LoonProtocolTest.php`)
* [x] `QuantumultX` (`QuantumultXProtocolTest.php`)
* [x] `Shadowrocket` (`ShadowrocketProtocolTest.php`)
* [x] `Shadowsocks` (`ShadowsocksProtocolTest.php`)
* [x] `SingBox` (`SingBoxProtocolTest.php`)
* [x] `Stash` (`StashProtocolTest.php`)
* [x] `Surfboard` (`SurfboardProtocolTest.php`)
* [x] `Surge` (`SurgeProtocolTest.php`)

---

### 4. Admin API Feature Tests (`app/Http/Routes/V2/AdminRoute.php`)
Feature endpoints that receive HTTP requests and manage the application state.

* [x] **Config API** (`ConfigAdminApiTest.php`)
  * `config/fetch`
  * `config/save`
  * `config/getEmailTemplate`
  * `config/getThemeTemplate`
  * `config/setTelegramWebhook`
  * `config/testSendMail`
* [x] **Mail Templates API** (`MailTemplateAdminApiTest.php`)
  * `mail/template/list`
  * `mail/template/get`
  * `mail/template/save`
  * `mail/template/reset`
  * `mail/template/test`
* [x] **Plan API** (`PlanAdminApiTest.php`)
  * `plan/fetch`
  * `plan/save`
  * `plan/drop`
  * `plan/update`
  * `plan/sort`
* [x] **Server Group API** (`ServerGroupAdminApiTest.php`)
  * `server/group/fetch`
  * `server/group/save`
  * `server/group/drop`
* [x] **Server Route API** (`ServerRouteAdminApiTest.php`)
  * `server/route/fetch`
  * `server/route/save`
  * `server/route/drop`
* [x] **Server Manage API** (`ServerManageAdminApiTest.php`)
  * `server/manage/getNodes`
  * `server/manage/update`
  * `server/manage/drop`
  * `server/manage/sort`
  * `server/manage/save`
* [x] **Server Machine API** (`ServerMachineAdminApiTest.php`)
  * `server/machine/fetch`
  * `server/machine/save`
  * `server/machine/drop`
* [x] **Order API** (`OrderAdminApiTest.php`)
  * `order/fetch`
  * `order/update`
  * `order/assign`
  * `order/paid`
  * `order/cancel`
  * `order/detail`
* [x] **User API** (`UserAdminApiTest.php`)
  * `user/fetch`
  * `user/update`
  * `user/getUserInfoById`
  * `user/generate`
  * `user/dumpCSV`
  * `user/sendMail`
  * `user/ban`
  * `user/resetSecret`
  * `user/setInviteUser`
  * `user/destroy`
* [x] **Stat API** (`StatAdminApiTest.php`)
  * `stat/getOverride`
* [x] **Notice API** (`NoticeAdminApiTest.php`)
  * `notice/fetch`
  * `notice/save`
  * `notice/update`
  * `notice/drop`
  * `notice/show`
  * `notice/sort`
* [x] **Ticket API** (`TicketAdminApiTest.php`)
  * `ticket/fetch`
  * `ticket/reply`
  * `ticket/close`
* [x] **Coupon API** (`CouponAdminApiTest.php`)
  * `coupon/fetch`
  * `coupon/generate`
  * `coupon/drop`
  * `coupon/show`
  * `coupon/update`
* [x] **Gift Card API** (`GiftCardAdminApiTest.php`)
  * `gift-card/templates`
  * `gift-card/create-template`
  * `gift-card/update-template`
  * `gift-card/delete-template`
  * `gift-card/generate-codes`
  * `gift-card/codes`
  * `gift-card/toggle-code`
  * `gift-card/export-codes`
  * `gift-card/update-code`
  * `gift-card/delete-code`
  * `gift-card/usages`
  * `gift-card/statistics`
  * `gift-card/types`
* [x] **Knowledge API** (`KnowledgeAdminApiTest.php`)
  * `knowledge/fetch`
  * `knowledge/getCategory`
  * `knowledge/save`
  * `knowledge/show`
  * `knowledge/drop`
  * `knowledge/sort`
* [x] **Payment API** (`PaymentAdminApiTest.php`)
  * `payment/fetch`
  * `payment/getPaymentMethods`
  * `payment/getPaymentForm`
  * `payment/save`
  * `payment/drop`
  * `payment/show`
  * `payment/sort`
* [x] **System API** (`SystemAdminApiTest.php`)
  * `system/getSystemStatus`
  * `system/getQueueStats`
* [x] **Theme API** (`ThemeAdminApiTest.php`)
  * `theme/getThemes`
  * `theme/getThemeConfig`
  * `theme/saveThemeConfig`
* [x] **Plugin API** (`PluginAdminApiTest.php`)
  * `plugin/getPlugins`
  * `plugin/config`
* [x] **Traffic Reset API** (`TrafficResetAdminApiTest.php`)
  * `traffic-reset/logs`

---

### 5. User API Feature Tests (`app/Http/Routes/V1/UserRoute.php`)
Endpoint features exposed to authenticated clients/users.

* [x] **Plan Fetch** (`PlanFetchApiTest.php`)
  * `user/plan/fetch`
* [x] **Invites & Referrals** (`InviteApiTest.php`)
  * `user/invite/save`
  * `user/invite/fetch`
* [x] **Notice Fetch** (`NoticeApiTest.php`)
  * `user/notice/fetch`
* [x] **Ticket Client API** (`TicketApiTest.php`)
  * `user/ticket/save`
  * `user/ticket/fetch`
  * `user/ticket/reply`
  * `user/ticket/close`
  * `user/ticket/withdraw`
* [x] **Server List** (`ServerListApiTest.php`)
  * `user/server/fetch`
* [x] **Coupon Check** (`CouponApiTest.php`)
  * `user/coupon/check`
* [x] **Gift Card Client API** (`GiftCardClientApiTest.php`)
  * `user/gift-card/check`
  * `user/gift-card/history`
  * `user/gift-card/types`
* [x] **Telegram Integration** (`TelegramApiTest.php`)
  * `user/telegram/getBotInfo`
* [x] **Commission Configurations** (`CommissionApiTest.php`)
  * `user/comm/config`
  * `user/comm/getStripePublicKey`
* [x] **Knowledge Fetch** (`KnowledgeApiTest.php`)
  * `user/knowledge/fetch`
  * `user/knowledge/getCategory`
* [x] **Traffic Stats** (`TrafficStatsApiTest.php`)
  * `user/stat/getTrafficLog`
* [x] **Order Client API** (`OrderClientApiTest.php`)
  * `user/order/save`
  * `user/order/fetch`
  * `user/order/getPaymentMethod`

---

### 6. Artisan Console Commands
Features executed via command line interface.

* [x] `BackupDatabase` (`BackupDatabaseCommandTest.php`)
* [x] `CheckCommission` (`CheckCommissionCommandTest.php`)
* [x] `CheckOrder` (`CheckOrderCommandTest.php`)
* [x] `CheckServer` (`CheckServerCommandTest.php`)
* [x] `CheckTicket` (`CheckTicketCommandTest.php`)
* [x] `CheckTrafficExceeded` (`CheckTrafficExceededCommandTest.php`)
* [x] `CleanupOnlineStatus` (`CleanupOnlineStatusCommandTest.php`)
* [x] `ClearUser` (`ClearUserCommandTest.php`)
* [x] `ExportProjectCommand` (`ExportProjectCommandTest.php`)
* [x] `HookList` (`HookListCommandTest.php`)
* [ ] `MigrateFromV2b` (No test)
* [x] `NodeWebSocketServer` (`NodeWebSocketServerCommandTest.php`)
* [x] `ResetLog` (`ResetLogCommandTest.php`)
* [x] `ResetPassword` (`ResetPasswordCommandTest.php`)
* [x] `ResetTraffic` (`ResetTrafficCommandTest.php`)
* [x] `ResetUser` (`ResetUserCommandTest.php`)
* [x] `SendRemindMail` (`SendRemindMailCommandTest.php`)
* [x] `XboardInstall` (`XboardInstallCommandTest.php`)
* [x] `XboardRollback` (`XboardRollbackCommandTest.php`)
* [x] `XboardStatistics` (`XboardStatisticsCommandTest.php`)
* [x] `XboardUpdate` (`XboardUpdateCommandTest.php`)

---

### 7. Queued Jobs
Job workers handling tasks asynchronously.

* [x] `NodeUserSyncJob` (`NodeUserSyncJobTest.php`)
* [x] `OrderHandleJob` (`OrderHandleJobTest.php`)
* [x] `SendEmailJob` (`SendEmailJobTest.php`)
* [x] `SendTelegramJob` (`SendTelegramJobTest.php`)
* [x] `StatServerJob` (`StatServerJobTest.php`)
* [x] `StatUserJob` (`StatUserJobTest.php`)
* [x] `TrafficFetchJob` (`TrafficFetchJobTest.php`)
