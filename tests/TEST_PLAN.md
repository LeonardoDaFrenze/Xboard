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
* [ ] `GiftCardUsage` (No test)
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
* [ ] `ServerMachineLoadHistory` (No test)
* [x] `ServerRoute` (`ServerRouteTest.php`)
* [x] `Server` (`ServerTest.php`)
* [ ] `ServerStat` (No test)
* [x] `Setting` (`SettingTest.php`)
* [ ] `Stat` (No test)
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
* [ ] `Auth\MailLinkService` (No test)
* [ ] `AuthService` (No test)
* [ ] `CaptchaService` (No test)
* [ ] `CouponService` (No test)
* [ ] `DeviceStateService` (No test)
* [ ] `GiftCardService` (No test)
* [x] `MailService` (⚠️ *Checks method existence only — needs logic tests*)
* [ ] `NodeRegistry` (No test)
* [ ] `NodeSyncService` (No test)
* [ ] `OrderService` (No test)
* [ ] `PaymentService` (No test)
* [ ] `PlanService` (No test)
* [x] `Plugin\PluginManager` (Instantiability and loading)
* [ ] `Plugin\PluginConfigService` (No test)
* [ ] `Plugin\HookManager` (No test)
* [ ] `ServerService` (No test)
* [ ] `SettingService` (No test)
* [x] `StatisticalService` (⚠️ *Checks method existence only — needs logic tests*)
* [x] `TelegramService` (⚠️ *Checks method existence only — needs logic tests*)
* [x] `TrafficResetService` (Fully functional test)
* [ ] `ThemeService` (No test)
* [ ] `TicketService` (No test)
* [ ] `UpdateService` (No test)
* [ ] `UserService` (No test)

---

### 3. Subscription Protocols & Serialization
Asserts configuration generation output structure, serialization, and content headers.

* [x] `Clash` (⚠️ *Checks class existence only — needs config output assertion*)
* [x] `ClashMeta` (⚠️ *Checks class existence only — needs config output assertion*)
* [ ] `General` (No test)
* [ ] `Loon` (No test)
* [ ] `QuantumultX` (No test)
* [x] `Shadowrocket` (⚠️ *Checks class existence only — needs config output assertion*)
* [ ] `Shadowsocks` (No test)
* [x] `SingBox` (⚠️ *Checks class existence only — needs config output assertion*)
* [ ] `Stash` (No test)
* [ ] `Surfboard` (No test)
* [ ] `Surge` (No test)

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
* [ ] **Plan API** (No test)
  * `plan/fetch`
  * `plan/save`
  * `plan/drop`
  * `plan/update`
  * `plan/sort`
* [ ] **Server Group API** (No test)
  * `server/group/fetch`
  * `server/group/save`
  * `server/group/drop`
* [ ] **Server Route API** (No test)
  * `server/route/fetch`
  * `server/route/save`
  * `server/route/drop`
* [ ] **Server Manage API** (⚠️ *Only `server/manage/save` is tested*)
  * `server/manage/getNodes`
  * `server/manage/update`
  * `server/manage/drop`
  * `server/manage/copy`
  * `server/manage/sort`
  * `server/manage/batchDelete`
  * `server/manage/batchUpdate`
  * `server/manage/resetTraffic`
  * `server/manage/batchResetTraffic`
  * `server/manage/generateEchKey`
* [ ] **Server Machine API** (No test)
  * `server/machine/fetch`
  * `server/machine/save`
  * `server/machine/drop`
  * `server/machine/resetToken`
  * `server/machine/getToken`
  * `server/machine/installCommand`
  * `server/machine/nodes`
  * `server/machine/history`
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
* [ ] **Stat API** (No test)
  * `stat/getOverride`
  * `stat/getStats`
  * `stat/getServerLastRank`
  * `stat/getServerYesterdayRank`
  * `stat/getOrder`
  * `stat/getStatUser`
  * `stat/getRanking`
  * `stat/getStatRecord`
  * `stat/getTrafficRank`
* [x] **Notice API** (`NoticeAdminApiTest.php`)
  * `notice/fetch`
  * `notice/save`
  * `notice/update`
  * `notice/drop`
  * `notice/show`
  * `notice/sort`
* [ ] **Ticket API** (No test)
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
* [ ] **System API** (No test)
  * `system/getSystemStatus`
  * `system/getQueueStats`
  * `system/getQueueWorkload`
  * `system/getHorizonFailedJobs`
  * `system/getAuditLog`
* [ ] **Theme API** (No test)
  * `theme/getThemes`
  * `theme/upload`
  * `theme/delete`
  * `theme/saveThemeConfig`
  * `theme/getThemeConfig`
* [ ] **Plugin API** (No test)
  * `plugin/types`
  * `plugin/getPlugins`
  * `plugin/upload`
  * `plugin/delete`
  * `plugin/install`
  * `plugin/uninstall`
  * `plugin/enable`
  * `plugin/disable`
  * `plugin/config`
  * `plugin/upgrade`
* [ ] **Traffic Reset API** (No test)
  * `traffic-reset/logs`
  * `traffic-reset/stats`
  * `traffic-reset/user/{userId}/history`
  * `traffic-reset/reset-user`

---

### 5. User API Feature Tests (`app/Http/Routes/V1/UserRoute.php`)
Endpoint features exposed to authenticated clients/users.

* [ ] **User Profile & Session**
  * `user/resetSecurity` (No test)
  * `user/info` (⚠️ *Only basic fetch tested*)
  * `user/changePassword` (No test)
  * `user/update` (No test)
  * `user/getSubscribe` (No test)
  * `user/getStat` (No test)
  * `user/checkLogin` (No test)
  * `user/transfer` (No test)
  * `user/getQuickLoginUrl` (No test)
  * `user/getActiveSession` (No test)
  * `user/removeActiveSession` (No test)
* [x] **Order Client API** (`OrderFeatureTest.php`)
  * `user/order/save`
  * `user/order/checkout` (No test)
  * `user/order/check` (No test)
  * `user/order/detail` (No test)
  * `user/order/fetch` (No test)
  * `user/order/getPaymentMethod` (No test)
  * `user/order/cancel` (No test)
* [ ] **Plan Fetch** (No test)
  * `user/plan/fetch`
* [ ] **Invites & Referrals** (No test)
  * `user/invite/save`
  * `user/invite/fetch`
  * `user/invite/details`
* [x] **Notice Fetch** (`NoticeApiTest.php`)
  * `user/notice/fetch`
* [x] **Ticket Client API** (`TicketApiTest.php`)
  * `user/ticket/save`
  * `user/ticket/fetch`
  * `user/ticket/reply` (No test)
  * `user/ticket/close` (No test)
  * `user/ticket/withdraw` (No test)
* [ ] **Server List** (No test)
  * `user/server/fetch`
* [x] **Coupon Check** (`CouponApiTest.php`)
  * `user/coupon/check`
* [x] **Gift Card Client API** (`GiftCardApiTest.php`)
  * `user/gift-card/check` (No test)
  * `user/gift-card/redeem`
  * `user/gift-card/history` (No test)
  * `user/gift-card/detail` (No test)
  * `user/gift-card/types` (No test)
* [ ] **Telegram Integration** (No test)
  * `user/telegram/getBotInfo`
* [x] **Commission Configurations** (`CommissionApiTest.php`)
  * `user/comm/config`
  * `user/comm/getStripePublicKey` (No test)
* [x] **Knowledge Fetch** (`KnowledgeApiTest.php`)
  * `user/knowledge/fetch`
  * `user/knowledge/getCategory` (No test)
* [ ] **Traffic Stats** (No test)
  * `user/stat/getTrafficLog`

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
* [ ] `SendEmailJob` (No test)
* [ ] `SendTelegramJob` (No test)
* [ ] `StatServerJob` (No test)
* [ ] `StatUserJob` (No test)
* [x] `TrafficFetchJob` (`TrafficFetchJobTest.php`)
