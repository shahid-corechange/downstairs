# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [1.30.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.30.1...v1.30.2) (2025-10-27)


### Bug Fixes

* false schedule references ([cb4623e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cb4623ec84eaacd52df06633fb8b90a1dfeb3f39)), closes [#801](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/801)

## [1.30.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.30.0...v1.30.1) (2025-10-24)


### Bug Fixes

* change schedule cleaning references in WorkHour model ([c930a7a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c930a7a10e16edf2bf4526afc913ea53f068bc3b))

## [1.30.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.29.0...v1.30.0) (2025-10-24)


### Features

* add confirmation dialog for incomplete customer information in EditForm ([bb0c641](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bb0c641691096ef78587c262a49ff662ed959e88))
* add notification method handling to user forms and DTOs for improved user communication ([b1c8d0b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b1c8d0b07d12366b3a5a34828eafc38d050cd0c6))
* add user info fields to customer overview and remove edit modal ([fe963ce](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fe963ce233a673c46d6bd996e592c179046fab2a))
* enhance customer and company overview to include notification method display ([b9d0514](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b9d0514c4628fb9d5b4db8e3e299ec3032f8d3a3))
* enhance ScheduleEmployeeController with caching and transaction locks for improved concurrency handling ([25d515f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/25d515fcb83f77f6959ec9ef7555fc84d564cfa1))
* enhance user DTOs and controllers to improve validation and address handling ([9d0dc3c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d0dc3cc1ba648efe8b4954ab6264dd77206e85a))
* enhance user DTOs and forms to include additional user information and notification methods ([07e77c2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/07e77c2039e62e760261bd7deadab142b6e22452))
* enhance user DTOs and forms to include notification method for improved user communication options ([91fe11d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/91fe11d511ec1884fa1d6ce5b2772923e4f4f196))
* enhance user forms and DTOs to improve address validation and notification method handling ([652745b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/652745b6c3fc9c13237cc71bd9601a90fd923589))
* improve address validation and customer completeness checks in forms ([8157ddd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8157ddd6b9d4ae683f6615b71e3d1c4950dcc3b7))
* improve ScheduleEmployeeController by adding status checks to prevent redundant processing ([7bd0bb2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7bd0bb2c39a8135a06b529696da5fc8fde5238c8))
* refactor company overview to remove EditModal and enhance user data handling ([2665886](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2665886c8ba928f19103f78d11fe07f9afd32bbc))
* update user DTO and forms to improve validation and handle optional fields ([52ab116](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/52ab1167a432bb54b150730d40c955f017c94afa))
* update user forms and DTOs to improve validation and user information handling ([48fba48](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/48fba480742f3984317795f457b885a580d9fe3b))

## [1.29.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.28.0...v1.29.0) (2025-10-02)


### Features

* update UserCashierPrivateCustomerWizardRequestDTO to allow nullable identity_number and adjust AccountStep component registration ([045fc13](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/045fc138fd5b21a6413bf2ab737e5837e74b3629))

## [1.28.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.27.0...v1.28.0) (2025-10-02)


### Features

* enhance CashierScheduleOrderController and OrderDetails components ([aad3482](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aad348229b711ef7e8d60724cf47efb1942ac564))
* simplify schedule query logic in subscription services ([c1892bc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c1892bcb93d6a3006fbebc24e79c7dd247fdcc07))


### Bug Fixes

* refine pickup schedule update logic in CashierScheduleOrderController to ensure status check for pending orders ([bb5dfe5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bb5dfe5c8be3b00712f44fc3cfe778c4ca487336))
* update pickup schedule handling in CashierScheduleOrderController ([f0c7a3f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f0c7a3fcd5f1aa65b66e1efe71368f8975295a55))

## [1.27.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.26.2...v1.27.0) (2025-10-01)


### Features

* add addons visibility notice to language files and update label text in modals ([d1c6cae](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d1c6cae4eed4fc47f9555312ac718c7769520baf))
* add message handling to LaundryOrder status change ([de11a52](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de11a525078c3bcae5be528d838cdaf3724bf497))
* add notification method to user info creation in Cashier controllers ([3e7f39b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3e7f39b79eba57c76630111c2f71b190375a6a06))
* auto-open receipt modal on successful laundry order creation ([2982a02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2982a02ebf13aab5b2543af0fb55802c973d2103))
* enhance OrderDetails with status handling and sorted history display ([4a6962c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4a6962c798eac341ce287cbaf5097f3870552a19))
* update Swedish translations and improve cart product modal functionality ([4ef7943](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4ef794384289d891e0ef5551d2e16f2804366809))


### Bug Fixes

* update rounding logic in cart components to use utility function ([208e1b1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/208e1b102c490d0b4f55f39488bcee993d0d39c4))
* wrong calculation laundry product cart on update ([ece3d87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ece3d8791ad3486c227decce502cc4b7d9f3659d))

## [1.26.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.26.1...v1.26.2) (2025-09-25)


### Bug Fixes

* update user retrieval logic in ScheduleHistoryController ([c326c8a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c326c8a52fbe6c73bcf632ee7b1d05d6afe0ff6d))

## [1.26.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.26.0...v1.26.1) (2025-09-24)


### Bug Fixes

* deprecate multiple API endpoints in controllers ([9d19960](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d199603e2f88d761f08007c4fa0bc27683b3f78))
* error create fortnox article ([9efaf9b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9efaf9b81e3d4ee0c2f613786138a6dc1ab37dbe))

## [1.26.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.5...v1.26.0) (2025-09-22)


### Features

* add 'stores read' permission and enhance store-related functionality ([c405e5c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c405e5c9e5a6676167c1d4c4d739541cab7a49ed)), closes [#702](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/702) [#709](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/709)
* add 'withRut' prop to ProductCatalogue for conditional RUT display ([35ea7a0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/35ea7a0bd666151dfd49b1953b0779a2956fdc50))
* add API version 2 support ([b721ba5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b721ba56c3b3d91f5b530686aa8941b92bc9985e))
* add blockdays functionality and enhance block day management ([a0bb1de](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a0bb1dec5f178fb65ade506f6301d1f84224e993))
* add cashier attendance functionality and enhance work hour management ([d316303](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d316303960b5b3537666813d412331bfd599d63a))
* add Category and Store DTOs, controllers, models, and routes ([eb0f0f0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eb0f0f05cef4031e4be25f095e78e8da3c6def87))
* add category field to invoice DTO and update related components ([27e5af3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/27e5af3a8587ef7a17ccb07a83b9b31fa6ab2b51))
* add category management and enhance order handling ([1c557fb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1c557fbdcbe93d0d89f703ebaff1fdd5a8250fce))
* add Category page and permissions ([9844f0e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9844f0e0a3d398684c3d07a970b0c7f9861ebccc))
* add change order status alert ([e8096c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e8096c94df32f55089d89c1ad2e7eb006ce83b2a))
* add Closed status to StoreSaleStatusEnum and update CashierSaleController ([55883db](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/55883dbc78cf2d9046e1bb48f628a6b5d815e5a3))
* add Customer Membership Type Selection modal before open Customer Wizard page ([795210f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/795210f94a2fa87a3832f31d0648e938d1cb9853))
* add direct sale page and cashier order ([f890913](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f8909130a0e9d570e17740c2b07d900c53d4c1e3))
* add dto ([c1715d1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c1715d145399fece57ce83f9a5c6fa9c869cd7c2))
* add fetch schedule by id API and fix unfind method name ([41a4492](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/41a44922a401ea0c30c12bf1309376cf537c7c34))
* add Laundry Order page and permissions ([94da3a4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/94da3a4f13be947cb2692cc09f7ade6bb7dc8d87))
* add laundry row description and improve cash invoice handling ([c5c03a4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c5c03a497c6266ce1b0c97e4415e5bfba3e391f9))
* add MigrateScheduleCleaningsByDate command and refactor migration logic ([7837bcc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7837bcce8568c987608d16eb3fc1b4d8082b0e45))
* add new translations and TodayAttendances component to Navbar ([5c00cdc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5c00cdc2aecf888095c35540123ed67c88fa82e5))
* add order source tracking to laundry orders ([ee421ad](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee421ad0651deaae06ca8952f0d8bc1bd31b2fdd))
* add paidAt field to LaundryOrder and update related services ([37420bf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/37420bf0a7b72806422f909578ab2972123e86f0))
* add payment modal for laundry order and direct sale ([892431f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/892431f9f878cbef90592503c2053506dc748193))
* add payment process and enhance cashier order and direct sale components ([bc214d7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bc214d74ea0b0f871c32aa94bf516bf782ee2c72))
* add permission middleware to order payment route ([a948d98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a948d9867830eba51b130259d6592847a2ceff25))
* add phone field to Store Overview and related components ([eefd967](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eefd967251702b8fb19741feabcd0928a49720f3))
* add polymorph relationships for subscription models ([e2d8a8d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e2d8a8d1aeb2d24f87780f49b9d6cfe8cdf5bbb7))
* add price adjustment message for fixed price in localization and update price type options in modals ([da30e38](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da30e38ced933a30db742e0c6c35b688f69e39bf))
* add Product page and permissions ([255f395](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/255f3952c955c6d71a4e4c96aa2bd0ab7efaaf5e))
* add product permissions and update category controller ([5c140e3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5c140e39f2ff0c38337026ac19c016cc77e4df6b))
* add product type to price adjsutment ([c918d3e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c918d3e31e8ba207dab0a9e01c7cc6ea08897e6c))
* add receipt printing functionality for Laundry Orders ([219407f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/219407f80bb72706a5e94922f10c875c6e58650a))
* add round amount handling in OrderStoreLaundryService ([7a7d922](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7a7d922675d41485e3741031f3e25bc8265d2cee))
* add round amount handling in OrderStoreSaleService ([92c08f1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/92c08f1c9b9ed660b63e29bf55b53d67fa22179e))
* add roundedPreferenceAmount attribute to LaundryOrder model and DTO ([f8605f0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f8605f08f61cdf61ca9feb3e29c94efb83769ddb))
* add rounding attributes and update financial calculations across models and components ([1d3507e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1d3507e462a2765d211f34e7d21d74ed0ee96298))
* add search laundry order and enhance the customer search functionality ([1191756](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/11917562945599334ee1ad0dace58adda267f6cf))
* add store management and enhance product handling ([7ad8b4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7ad8b4df50dbd2507e822efa470393ccc35e667d))
* add Store page and permissions ([693a03b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/693a03b0cea05f23d2675e73a5688966fba03adf))
* add store sales functionality and enhance product management ([7cb8038](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7cb8038da7235c478ad8fa8a2307fde301bae5dc))
* add store selection modal, cashier layout, cashier search page, and cashier new customer wizard ([ec1e42c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ec1e42cc4213937e9b5253bb973f231aaad76925))
* add subscription schedule service to generate schedules ([764c737](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/764c737a19d93b5de826aa3beb052caedd918f81))
* add type and laundry order product to fixed price forms in customer ([08ceb70](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/08ceb706446b8283a8964dabf2922ebaa8b93e45))
* add validation for quantity, price, and discount fields in cart modals ([8880799](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8880799f8ea35c46490a43f4ec7db483ca4d18cb))
* add validation rules for new add-ons and products in DTOs ([f7959fc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f7959fce8be68ce91209a7f19381e09e12a427b3))
* api v2 ([814f015](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/814f015885492b19d52f9608889e56b5d9bf1c7b))
* change unassign subscription structure ([c603372](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c603372fe430e7451456149101fa7cfacf1507f1))
* create Category and Store seeder ([a632800](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a63280032469129436c8bfa5cdd334202e47b71b))
* create laundry and sales migration ([7f72ffb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f72ffb08e67b8dca7ad26fadf2f684ff233de62))
* create model ([ce5d397](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ce5d397db3c55dabf891203e5e3f318fad2bf651))
* enforce payment method consistency in laundry orders ([0aa57df](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0aa57df88121ffdcb9b77795a67e56acf1490fb7))
* enhance AddOn and Product pages with categories and addons support ([e486da9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e486da909e134724c692522d4f4a34501ccaa59f))
* enhance card payment handling and store selection modal behavior ([1dc28cc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1dc28cc105ef2bbec957d39aacef5c9459ecf8db))
* enhance cashier order management with API ([747a87d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/747a87de6b9d28caf0c0e663cc7e2521db6da10d))
* enhance cashier pages ([4ce4d51](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4ce4d51154a51e0800b3837b5b7f4dcd85aaa858))
* enhance cashier sale functionality and improve product handling ([77dbd3b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/77dbd3bec244f83e90b2cbf72997321140a3e886))
* enhance Category page, components, controller, and DTOs ([18673f3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/18673f3f4984647971936ae90280638c72bba94b))
* enhance checkout error handling and product display ([6a7d164](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6a7d164dac6b6d97b6352c3b8ff1f56517abb640))
* enhance financial calculations and component logic in LaundryOrder and Cart ([ab37d75](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ab37d75854be0e573380020072811087b3ccb09e))
* enhance fixed price eligibility logic in OrderFixedPriceService ([71acb61](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/71acb61816f67245aa446ff2a5d83c0a170e4314))
* enhance laundry order creation and scheduling functionality ([db51443](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/db51443fb2e88e0c975c1498f0a9084ea015023a))
* enhance Laundry Order details with schedules, histories, and permissions ([2b836f1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2b836f1bbf79b6a30c7192efe3e79c41b67b8a82))
* enhance laundry order functionality with messaging support ([ccfc8cd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ccfc8cd1ee0cd588b9fbb0c96d4d3ad999f5155f))
* enhance laundry order functionality with schedule collision handling ([aae0f80](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aae0f80ccddb6ac3f5af3843b00035af3692214f))
* enhance laundry order model and response structure ([4edd2b2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4edd2b2a8bb6e38ba137430ba49ca0c5681f6400))
* enhance laundry order model and response with new fields ([270c186](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/270c186aecd4e487b88bb7d5f2dad9a1f9b12213))
* enhance laundry order processing and receipt generation ([6bf9b15](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6bf9b151c1da344ef069e15fc77c9ed22530c8e7))
* enhance laundry order processing and scheduling ([ba07f08](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ba07f08d159d3f981f1d405a27fe9777b25ecaeb))
* enhance laundry order processing and validation ([da20a1e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da20a1e4cccce961fe8d181be7505542fbf4b931))
* enhance laundry order request validation and preferences retrieval ([012c022](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/012c0229af589e6e749eb2f674453e9e43bddba3))
* enhance laundry order scheduling and notification system ([c8f094e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8f094ef8430c231f4953d02302798a2fb741cee))
* enhance laundry order status and scheduling management ([9106e98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9106e982d2e0b788af0c587d3cbf2debe25da048))
* enhance laundry preference model and response DTO ([07f0d0b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/07f0d0b7a2ac12d71e6cc18a37c7cc02d092cf3a))
* enhance migration commands to accept ID range and improve logging ([c76eb70](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c76eb7032fb9a58a0dc89f6992a012e81ed4446d))
* enhance notification and laundry order management ([94967c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/94967c99c8706a65aa921b3a2f06773e27020c4c))
* enhance product factory and migration with enums for unit and VAT group ([bd7bb21](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bd7bb21bc5a6d4c2c3907a06f0ffe9515ef5a81f))
* enhance product handling in CashierSaleController ([77d0f60](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/77d0f605c20b5486442dac0af394c40e25c6c65f))
* enhance product management with store associations ([7f19613](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f19613b2c2a2134bdd39dedc1b97741b3e724e2))
* enhance product validation and modal behavior in cart components ([278588e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/278588ecaeb6384c39efb56799b7a241b121a6ba))
* enhance ScheduleCleaningResponseDTO and related components for laundry integration ([abdb93d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/abdb93d9daade78347d6bc4a8531d44633833c09))
* enhance scheduling logic and error handling in laundry orders ([b16e695](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b16e695f64c29fa58598e07719c995235fa0d933))
* enhance store and customer management with new DTOs and routes ([5e2245b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5e2245b95eabf8b2d992efef578352c57b20314a))
* Enhance Store Management ([aa1a168](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aa1a1686f0a49162a51bd9a3194463195e5142c1))
* enhance store management with user associations and update DTOs ([dfcfdd5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dfcfdd515c486e0ba64bba1fb131160970dbe4ec))
* enhance store sale functionality with customer ID and order integration ([7b93acf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7b93acffe19c9fb61d8480d5e63b6b78df01c634))
* enhance store sale management with payment method and metadata support ([3aece64](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3aece64c0a8cf99a239a4f2ca6a4d49b1ef2b0b2))
* enhance subscription and schedule handling ([f00ef0f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f00ef0fd9b4bbf8c93db0b549fe152e023e775e8))
* enhance success handling in ChangeScheduleModal ([d2ff6da](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d2ff6da26e0fb08f210184c8559a9e422ee2da28))
* enhance user cashier customer wizard with validation ([9050ed0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9050ed0c113071955a7d1f95057e242751b09852))
* implement cashier attendance features and enhance employee management ([884e488](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/884e48880b233752cd9da649f4e5d44330932983))
* implement cashier customer cart functionality ([ad6f307](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ad6f3072f98b5ad73391f188d86cce2b1242ceb4))
* implement cashier role and enhance customer management features ([de4e74c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de4e74c4f20895801b68372792b0083882b40289))
* implement change pickup functionality in cashier order management ([eef5a22](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eef5a22d1d387b1d1d0c77d13ac66da9e6956c0e))
* implement direct sale cart validation and automatic navigation ([abc9f5d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/abc9f5d3205861ece190b110b52506d95820b5b0))
* implement fixed price on cashier cart, checkout, and details ([b9e2b8c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b9e2b8c92aab97aa1288e09a1336df81344d9719))
* implement full CRUD operations for Product controller ([6f8a83e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6f8a83e2ae2de092860da54b19fbf538c73d4eb6))
* implement laundry order creation and update functionality ([fe606b7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fe606b7fb2ae33837768d5868b5d9cc56d12c298))
* implement product retrieval and quantity update in cart hooks ([fe80e81](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fe80e810048fd5beed978e25ddc661f78f796236))
* implement store selection and change functionality ([b64e480](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b64e4802e15c056a373277c58fdbe0102dc8c4ac))
* improve product handling and schedule validation in CashierScheduleOrderController ([ed61ce5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ed61ce5534e8a10939f250385c2f9439e38d3c9e))
* include service relationship in schedule retrieval for checkout ([6333878](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63338784d6a5a76ddbfb8d4b447c5a0bd83f438b))
* integration Product with BE and refactor Addon and Service ([2a91c53](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2a91c53d0c2f63749965e5b8bde7e682d4c349ad))
* laundry subscription in wizard ([2b16303](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2b16303598d066a21df9418e9806fae2c53f7306))
* laundry subscription update, pause, remove, continue and restore ([fd5181c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fd5181c572bbba8432cb634d847ac29476a0da62))
* update CashierAttendanceController to filter by store ID and change to closed in payment action ([a1b26f3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a1b26f30b55e9ac0eb2538395418ad6533d6d3a4))
* update laundry order and customer DTOs for improved data handling ([47f00eb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/47f00eb5399a38ff2839a4c62efa14c9129708b4))
* update Store Overview page with dummy data and status column ([a76bd29](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a76bd29ea77094aae1f991caf2ae5aebc08a4578))
* update unassign subscription DTOs and controller for product and addon handling ([7107fd2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7107fd2c7c75ef42c71c114a631c81f2cc35b55e))
* update user cashier customer wizard and controller logic ([0c12019](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0c120191c7d565871f331741a2f59f292bb18ecb))


### Bug Fixes

* correct error response formatting and enhance schedule creation logic ([57e9cae](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/57e9caed56e8af7624f4445de6524c0f2c046e84))
* correct team validation logic in ValidTeam rule ([fff85ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fff85ab9695cd21c76d3eb4320037189dc8d5014))
* enable product creation in AddProducts command ([0e60074](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0e6007428d58128ea9b3a53147ce6c2ccbab353f))
* update middleware for order payment route ([ac253f3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ac253f371d8fa2831607b842246ad2baed0541ba))
* update round amount condition in OrderStoreLaundryService to use absolute value ([b85b683](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b85b6839e683a6d8923ab582cab4216115407135))

## [1.25.5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.4...v1.25.5) (2025-06-23)


### Bug Fixes

* add fetch schedule cleaning by id ([af34ecf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/af34ecf07f38b40ec663f7e24f695f61f604fccd))

## [1.25.4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.3...v1.25.4) (2025-05-28)


### Bug Fixes

* error update fixed price row ([3408613](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/34086134584cb271d0b4fdf36d165b0cb7312805))

## [1.25.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.2...v1.25.3) (2025-04-09)


### Bug Fixes

* error handling in Fortnox resources ([e5f2e2a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e5f2e2a76b37d79b36d24997b3770e31b640c443))

## [1.25.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.1...v1.25.2) (2025-03-11)


### Bug Fixes

* misleading credit refund notification ([c0c2d4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c0c2d4de71974a2beaeee484b1f04746defdae73))

## [1.25.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.25.0...v1.25.1) (2025-03-04)


### Bug Fixes

* bug different day of week when generate schedule ([755bc02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/755bc022e89ae925ae092f474b0a9198e1bfc93e))
* bug generate schedules ([094cdbe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/094cdbe7f4f04fe2a3445dad1dba9441f533bb22))
* bug generate start at schedule cleaning ([fc372fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fc372fe7bc7658d12737e568877aa2bbf9c959b6))

## [1.25.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.24.0...v1.25.0) (2025-02-27)


### Features

* enhance DataTable with date range filtering ([38d6362](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/38d636230ec80d9303189841d974689198eac8c7))
* optimize monthly work hours ([266f092](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/266f0921fb6c7c9d9f1e169427602ac8dff59116))

## [1.24.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.23.0...v1.24.0) (2025-02-21)


### Features

* add subscription refill sequence configuration ([bf2e964](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bf2e96487937943b671cbc97842499a1cd100408))
* add subscription refill sequence configuration ([a82fdfc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a82fdfc92ac97b7ccc7f4b9452b3256994716a61))
* adjust refill sequence for schedule generation ([5d6fd21](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d6fd21cc65e622b8c3a484f4be558bdc79a0515))
* implement max creation day limit on reschedule ([b778614](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b778614f61a95669360df0f57e0c58b73f6217d1))
* update initial start at schedule cleaning ([fae0a46](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fae0a46bd7c7e55d1b306fa317e8ad0d3c649130))

## [1.23.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.22.3...v1.23.0) (2025-02-14)


### Features

* add booking hour ([4df528a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4df528a15677756fed481068c47dfd0ce05d79cf))
* change subscription one time notification ([538a58d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/538a58de9cd9dd47803cd2a31380777325a34dc6))
* turn off booking cancelation ([b0c8210](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b0c8210e6ca34f5da54fcd22fdb0d4acaab6ff2b))
* update monthly workhours view table ([5058311](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/505831148373c280aaa40009758a1357892da420))
* use job to update data in fortnox ([bef74a8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bef74a8b50702b873e2bf2956acd72dbd8d13acd))


### Bug Fixes

* error get user in app price product accessor ([868c117](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/868c117603be9761b95845805bc26b477ee0f59f))
* typo on supervisord logfile directory ([77efe94](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/77efe941056264db1f86a7ffb95a6a14844eb5f2))

## [1.22.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.22.2...v1.22.3) (2025-02-07)


### Bug Fixes

* bug can reschedule accessor ([4f361a3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4f361a31a54e1c1b482b0cde6d35a8cf0e5069a6))
* improve schedule overlap detection logic ([765df1e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/765df1e32550d3512835420c2323c7ca52f367b5))

## [1.22.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.22.1...v1.22.2) (2025-02-07)


### Bug Fixes

* 15 min schedule interval cause attendance issue ([98cf049](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/98cf049af6eee0ece0553db1586dd5cd87e55ba4))
* 15 min schedule interval cause collision ([4c49f40](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4c49f40f894407def11ed1af1673f5344df2f314))

## [1.22.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.22.0...v1.22.1) (2025-02-05)


### Bug Fixes

* failed to create invoice when description or unit is null ([c04899a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c04899a83fec9ae4fc32910592bde4f18b769257))
* fixed price start date and end date not showing ([28418cf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/28418cf8751b5a2f4c8d9f6ade26809e1c73d40a))
* missing null check on order fixed price ([1722b2f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1722b2fec544729f47b43861de507efd282dbf26))
* missing only clause when getting available workers ([201d478](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/201d4789652aa134ba507814d493c098c4e4b40f))
* order header not showing properly ([fd959f9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fd959f98efc52631623e8024fe921f5ef0121e0d))
* remove unnecessary field ([fec6096](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fec60964a198404eaabc19aecc557a3d98e35e84))
* revert json show deviation ([85e2d7d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/85e2d7d3696dcf0c1aa792f6ca391721f4819e88))
* work hour widget display incorrect value ([646257e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/646257e80c3e37bab07d41c81c6b9c63674766b5))

## [1.22.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.21.3...v1.22.0) (2025-01-31)


### Features

* add action in invoice summation to see the related invoices ([fa18ab2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa18ab2200b3968e176daa6af288ece95196e73a))
* add action to preview invoice ([5a8d45e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a8d45e427b5577fede3ffb0c1368da6b3746003))
* add filter schedule status to query string ([2a99106](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2a991060aeb96141ddc367389ae43f2de9ddd8fa))
* add fortnox invoice id, customer, and send invoice method field in preview ([d598a86](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d598a86cfb4063be61093e2b1af83f224be79b7b))
* add new status on change request ([d87ad90](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d87ad90d4da801a1e6956ffd55eb763e91fe63f7))
* add refund credit when update schedule cleaning ([ea935ca](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ea935ca699986efe89bc0cfe66d621f080e1dd3f))
* add revert action in cancel schedule ([ef315c0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef315c0368e9b14843163b0806b2186cc1394869))
* add separator between order ([fd663c4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fd663c4f7925c11932de91edc0ab791b6ee38018))
* add total credits on user ([cdec2c8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cdec2c88289cce8f895ccf1786fdad7f813839cb))
* credit handling on schedule edit ([d8822fd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d8822fd4236840553e827491c144c08655970973))
* improve preview invoice UI and create the backend ([86c32ec](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/86c32ec189c6308e4759524ffcccadb481e19b63))
* update invoice summation when fixed price or order updated ([8bef06c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8bef06c27abfc563ccef0c680701713b449cb2ab))


### Bug Fixes

* handle optional data in deviation summary ([141c2c3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/141c2c37c02e64840bb42ab7df2d9de7d284b29f))
* make interface-type properties optional and refactor affected code ([d5181ae](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d5181aefc830b05ab64c53954d906c42a795d956))

## [1.21.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.21.2...v1.21.3) (2025-01-07)


### Bug Fixes

* missing property address2 ([f96d8b3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f96d8b329e1711a7a8a594076a5cc2a97cf72a57))
* remove min max date range filter canceled booking ([23af7ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/23af7ab6fc4071d63414a9477495428156cb3f8d))

## [1.21.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.21.1...v1.21.2) (2025-01-03)


### Bug Fixes

* wrong calculation on total rut ([017f327](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/017f3275f81526f99a24b46d483f406a4b12b165))

## [1.21.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.21.0...v1.21.1) (2025-01-03)


### Bug Fixes

* created invoice will not be updated because it never sent by fortnox ([b575109](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b575109a76a7b59da68b91d83f2a962907192069))
* missing column invoice summation table ([2c3af3a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2c3af3a2be15ef0eadf546a49c5c64fb682380e0))
* send_at year not set correctly ([d0e14f0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d0e14f03f3e6ce0a68548e7394491833912911ca))
* wrong summation calculation and unclear detail summation modal ([a62e0d2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a62e0d22601fe8da365f67d270ba33dbc04f7697))
* wrong translation fixed price text on invoice ([d68c4e2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d68c4e2d24f1297645252e8155b8f86dcb899547))

## [1.21.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.20.1...v1.21.0) (2024-12-28)


### Features

* add canceled bookings and invoice summation cards ([ddcb993](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ddcb993ab0884130bb04ac696f2d445c265b6f8d))
* add invoice summation table ([a7325a7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a7325a773d89f55211273a1ca4d9e20f7966b571))
* backend schedule cancelation and invoice widget ([5046929](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/504692955fbf87f5104343e18f0767eefc5c2d42))
* enable update personal number ([fdb564c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fdb564c2bc887ba9ad89b62530459084a3c9ac62))
* make it possible to update team, start date, and end date of schedule ([c7edabb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c7edabb3ea5da5c4ca830747e9dfd3bbe0fc94b4))
* remove property row if has fixed price ([55be277](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/55be277243a32b4b8492f079891d00b06ab198d5))
* sync user and primary address information ([9377dc2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9377dc2afacce9b08ac0bea79fd2cf38818a8b87))


### Bug Fixes

* fixed price not applied on old database order migration ([b00e279](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b00e279b350212d80d06446357bb537389f63257))
* table showing all employees instead of the active employees ([ffb39b8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ffb39b8c22c62220e2fe5132e3063f4c6da6879c))

## [1.20.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.20.0...v1.20.1) (2024-12-19)


### Bug Fixes

* missing nullable on property keyInformation type ([a3fca17](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a3fca17022174800508cd00812a8a0953cdeecc6))

## [1.20.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.19.2...v1.20.0) (2024-12-17)


### Features

* add email and sms as another way of sending notification ([7f2c9c6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f2c9c6d498af29198a4bbfccf0936dae3485633))
* add fixprice field on private and company customers wizard ([8d6c02b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d6c02b6be1760125a66ce96a75598c2e1a816bb))
* add header row when has monthly fixed price ([6f24088](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6f2408839725bb259b83a5b102851f4b0858fcfb))
* add property tab at customer and company card ([8cc5c45](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8cc5c4590eb963038f8a0fbad77b394a5ccda967))
* add view schedule on schedule tab in customer overview ([ff922c5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ff922c5a77d0d246270739b62ddbabd65a8553f1))
* backend assign existing fixed price ([1d40723](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1d4072399ac952d6929f3cbd6be5caea7ec01612))
* make send notification job more flexible and fix cache and template issues ([237738e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/237738e8810cd9bd169ff3e2b9626c26e08b61d0))

## [1.19.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.19.1...v1.19.2) (2024-12-06)


### Bug Fixes

* API endpoint does not response with required fields ([5082f32](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5082f3218573517aef29a8f71bf30159fe067532))

## [1.19.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.19.0...v1.19.1) (2024-12-04)


### Bug Fixes

* polymorph type not updated when type changed ([c510118](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c5101184786bb87f29b62213dd31994ba28987b5))

## [1.19.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.18.0...v1.19.0) (2024-12-04)


### Features

* add key place on key information in schedule modal ([4834bf8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4834bf8fcff4f5e47e61bfb3dce9673838ea2645))
* add name column on price adjustment row and some other minor improvements ([5a127fd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a127fdbe8091cf3278b76c4b00cba2f4b9a8f7a))
* add price adjustment scheduler ([4485d2e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4485d2e1e5552f95f8b95dc0a8f2eaeeec68c112))
* add property row and update material per hour ([b6da1b3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b6da1b3c4ceb7448bdca599a3c976a2049e8b0fe))
* alert edit quantity service and material row ([d29565f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d29565f04e3764137c7294e9bc135a8c5e846fe5))
* Change piece to hour in service row and backend sync material and service row quantity ([d558fdc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d558fdc752ede52fa7a0f8a0f4e2a2ff620e5c72))
* init backend price adjustment ([7030016](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7030016988009732f0315c13a720992b0304dbda))
* migrate service row to hour ([41240e1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/41240e1403c097f8a93708dd9c7d0f7f381e9fd0))
* price adjustment ([3f50907](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3f5090786e7b3c60e37e4de1c0f294c5e502995c))
* price adjustment initial commit ([cc29642](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cc296423ed75d671f8ace604f4f112dc3441eb5b))
* update quantity data type ([bc0ac1f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bc0ac1f815cd292177f40d1dc83b6b7835f7fd3e))


### Bug Fixes

* company credit endpoints ([1834689](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/183468969c371202c28cc157ad6db1224c4bfda7))
* incorrect badge colors, row action still showing on done, new price not include VAT ([6b3868f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6b3868f9fba0a8002bca72d8b7813f53270fce60))
* incorrect behavior when check schedules collision due to DST ([1bf64ce](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1bf64ce031cf21ab614456b80604a8ae6d3e0834))

## [1.18.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.17.3...v1.18.0) (2024-11-25)


### Features

* add clickable fixed price badge ([c0e3847](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c0e384710a78b1aff1cc515448b07d2d910bb879))
* add credit description row in invoice ([cc728c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cc728c928b181cd6cbf2c797af432f6db3413559))
* add rule employee start job in the current day ([442d4a0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/442d4a01465ea20d7095598bcfd73e99d28d4fe6))
* change the order list of schedule history ([585eb27](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/585eb27e46dd912605b02b64ed0e6a5fde657cdb))
* merge unassign subscription cards ([b893461](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b893461646ddb65191ee80f945b2729bd9035089))
* remove fixed price and discount from dropdown menu ([f84d204](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f84d2049cb959dee50b061ad9dc75fa0c751ecf0))
* update customer modal size and global modal padding ([5e9deb6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5e9deb64e06ac6033770624f140709c91f35cfcc))


### Bug Fixes

* deviation widgets not sync with the actual data ([62328cb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/62328cb17952809aafdea141ad0a0007188bba2f))
* duplicate orders when import from old db ([60cdd1f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/60cdd1fab45743f1f96051286a2174dab70f2259))
* incorrect way on setting the created_at value ([623e8ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/623e8abfbc5d47ae970acddb2b475025a81432f2))
* order fixed price duplicate and race condition ([d1816fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d1816fe103dc54b048368887e3eab937a24ae3f6))

## [1.17.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.17.2...v1.17.3) (2024-11-12)


### Bug Fixes

* invoice failed to create when fixed price is soft deleted ([b718c6c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b718c6c9f5f3ef45ebf7e504658f012860f24cc2))

## [1.17.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.17.1...v1.17.2) (2024-11-08)


### Bug Fixes

* create tax reduction recreated when send invoice ([c783700](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c78370085b9b4c33e25a8eb9661f74058acbcbd1))

## [1.17.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.17.0...v1.17.1) (2024-11-08)


### Bug Fixes

* soft-deleted subscriptions not included on the loop ([5477531](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5477531782d8a666615e5221cfea3319b8dc79fd))

## [1.17.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.16.1...v1.17.0) (2024-11-08)


### Features

* add button revert deviation ([f1b1bf6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f1b1bf638de6e6fdb99ca3a2de2e6482d838ce8a))
* add fixed price apply to subscription orders ([97b30af](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/97b30af4842eb02a17f1382c25f181e8bdba9c45))
* add revert endpoint and show canceled employee on deviation overview ([c96642f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c96642f40f7a2cc2bfd9d41e24ee57e44e9ce8f6))
* add revert worker status button on Schedule modal ([c6af376](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c6af37675f25088b268903156317ca92ab0e62f8))
* add revert worker status button on Schedule modal ([e0b99ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e0b99ff41c689f6403db2cf75caed1925fee45ec))
* adjust order fixed price row when fixed price row changed ([24ade14](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/24ade14421930ed82c0a06cc73af4f86255fb93d))

## [1.16.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.16.0...v1.16.1) (2024-11-07)


### Bug Fixes

* validation key information ([f8275fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f8275fe7795f355f511301d9f1317705ab29630d))

## [1.16.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.15.1...v1.16.0) (2024-11-06)


### Features

* add customer reference and address 2 on invoice address also optimize company invoice address ([0e3220a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0e3220a2496c3017ea5531b3625ebe28dfdeba77))
* add notes and some address related improvements ([f92235c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f92235cb6f3e049c5e5450844f179c5ae6c0196f))

## [1.15.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.15.0...v1.15.1) (2024-10-30)


### Bug Fixes

* incorrect time on schedule generation after normalization ([c40c57e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c40c57e4b96445aa5f02951f2f1a8abd164a6322))

## [1.15.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.14.0...v1.15.0) (2024-10-26)


### Features

* add backend and database time adjustment ([6ec5291](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6ec5291aad066787a72da2c935e0c7fb16be83cb))
* add create time adjustment and update validation ([796977c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/796977cdf48302c522df07d29c0e8ad354a84d65))
* add edit form and delete time adjustment ([8c66a99](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8c66a99b3ae21754d2b7708516c10fec51ba0f76))
* add endpoint update and delete ([de94075](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de94075b28006873d96acde7b4eb542075e9beba))
* add start and end date column and add transport and material to fixprice ([a9585a6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a9585a63b5b7af5b87e83e4afe5ed5d9152d06ed))
* add start date and end date to request DTO and adjust fixed price controllers ([2509236](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/250923670040b4deb2fd1f1706a09d297d5de92c))
* add start date, end date, and is active attribute to fixed price ([62ea9e4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/62ea9e49c509ea9a3733e2da7c211c3c5ae69918))
* add time adjustment form ([c86c3d5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c86c3d56461076ab6d87be20fb4c6395a2d9a58e))
* add time adjustment panel ([7edfc4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7edfc4d8cfa93ef609bd11d5ae020cc61cc7181b))
* apply discount and fixed price when order created ([b782dbf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b782dbfb8afd38ab5db4f7bc561e72436563c7e8))
* create add time adjustment form ([ea8845d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ea8845d0c08bf3a6a394d7d3aff0d66cf853a4f3))
* new monthly work hours and update query string trait ([ec6d727](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ec6d727b756fe5e4284c9fb41ca45cacdf836e95))


### Bug Fixes

* missing translation for partly canceled deviation and deviation duplication ([139e6f2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/139e6f2bd3c62757c2728251df84e76d0e408aed))
* schedule time changed because of DST ([aeee92a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aeee92ae8d01f8e084ce9052b84ca980c720d032))

## [1.14.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.13.1...v1.14.0) (2024-10-14)


### Features

* add cancelable columns to schedule cleaning and fix some bugs ([10409d7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/10409d7c36e84f5a8ac9a793a56f377b5eb41bf9))
* booking will be cancelled if all workers cancelled it and worker get deviation for cancellation ([360bfee](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/360bfee5f211e1094eaf121bf7fa651784f5490b))
* update recschedule needed accessor ([bfb742a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bfb742a55225f73fc58a829cfd889fbfb231ddff))


### Bug Fixes

* bugs found in production ([9d97cb0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d97cb0476c59f42e1bbda59e259039b30729512))
* downgrade php-redis to 6.0.2 because latest is currently broken ([fb927c0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fb927c0ba8388bb1735fff4b207b1730e099d95b))
* update schedule cleaning product block unreachable ([b163d0b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b163d0b83bc709bc13313889e615951bdeec6723))

## [1.13.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.13.0...v1.13.1) (2024-10-09)


### Bug Fixes

* insufficient memory on leave registration ([1e567a7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1e567a77f429c247136e2d13ede373737721ada9))

## [1.13.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.12.0...v1.13.0) (2024-10-04)


### Features

* create a script to recreate all customers in Fortnox ([8ab4106](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8ab410671b7e96ed37ac029790670786ff8593f2))
* create schedule history panel and change translation of unapproved ([7024d9a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7024d9a1ef9f5318073dd0f130a9cc146a4febd2))

## [1.12.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.11.0...v1.12.0) (2024-10-02)


### Features

* add invoice method on front end ([40169d8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/40169d8f5c0acb8e5afc3fb716fb3df051d6c8a4))
* add migration and backend ([067047b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/067047be2e77605cc5f7029dd60a7e71cfb9dfab))
* add shortcut to customer view modal from schedule modal ([1547182](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1547182ae82d2f0a747cc30de0cff9829a1a76e2))
* improve invoice address ([db8005c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/db8005c06d55a2d4d5bf48834687924c4e23a28f))


### Bug Fixes

* wrong endpoint and translation for discount panel company ([e1e3f55](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e1e3f55a516811f1040b08f07c0b727f89b26af3))

## [1.11.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.10.0...v1.11.0) (2024-09-23)


### Features

* add addons statistic and date range picker on client table ([85ae2ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/85ae2ffb90eea6bf3b3c663d55eb15b91616b6ed))
* add discount and fixed price panels in customer and company view modal ([87acb09](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/87acb09fbc6b0cc0c8fbb43ef80b70875525214a))
* create add on statistic endpoint ([3b632e5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3b632e5ae14f410353a807dcbd4c70e3fef6e568))
* init add on prices ([8d07e5b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d07e5bbfe45826075a327eac6f2a296e8aef5ec))
* schedule add ons backend ([1e9d682](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1e9d682166ebc59476b01c11a4a599d27374e27b))


### Bug Fixes

* booking can't be dropped on canceled booking cell ([06e5be8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/06e5be89aae25765aadabeed3e4c6328996c8d7e))
* change query and datepicker ([9cb446d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9cb446df7e41849beaa52ec259e88445ab1149d8))
* wrong message format when sending android notification ([96917d0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/96917d0763313be242a4148bc75dbd5b5e171cdd))

## [1.10.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.9.1...v1.10.0) (2024-09-18)


### Features

* add MaxProductAddTime on global settings ([a15f878](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a15f878ff3e2396eeddc357c8a439eba9ae84dc6))
* add sales account when create article on fortnox ([6884aba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6884aba60ac857c8d47b6eb43faf59541ab34d84))
* add validation when checkout product ([dc2e977](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc2e977aa0e6946dc78d78e8ad4947d24f57a0e1))


### Bug Fixes

* leave registration implementation not following the business process ([53ce36a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/53ce36af1093d9f8a9184e519cec167eebe1bf10))
* leave registration still incorrect for some edge cases ([9a4ea46](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9a4ea468e660e45b22ddece7f985170f2d46c46e))
* schedule cleaning end time not adjusted when employee deleted/restored ([adf9885](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/adf9885cd8a66b67030d5767b1ecbc8ecbe1c308))
* schedule cleaning end time not correct when restore employee ([fa6219b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa6219b021ab2a4f82d0db050a5a6fd37151f8c0))
* sync fortnox error if article price date is missing ([cc16c85](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cc16c85b038f3751a86a623f9d8d3b6d48e49fde))
* wrong condition to check for time limit ([6f46f72](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6f46f72fbd6a6d75e0ae36586e240b22ede2933b))
* wrong condition when showing alert on type changed ([ba89702](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ba89702fe897a24f8ce1d12fc02d72213490475d))
* wrong type on sales account property ([b42ee48](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b42ee48ee50474c0bf64b501ce8419a1ed0649b8))

## [1.9.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.9.0...v1.9.1) (2024-09-09)


### Bug Fixes

* custom task not added to schedule cleaning task if schedule is in progress ([c360c7b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c360c7baafacc0b934c15b87ae9e4d37796a5157))
* filter not displayed as autocomplete on change request history ([8410b59](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8410b59f8969875bd5dd9e149bbf3b984ada0e76))
* form gone when identity number not valid ([cb4049b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cb4049b59c92418435358d83bde0f8a7833eb06a))
* map did not show the correct location on edit form ([58c4a24](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/58c4a24a1fa2182e961df0d70bf898d601ae88cc))
* namespace typo, not invoked enum, unnecessary validation rule, and wrong modal name on leave registration ([8d1f974](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d1f974091da4ae652444a19c965e003b219a5ad))
* note not updated on the apps ([6d7182d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6d7182d60f897124112ab8d3d9562a8df86d7c86))
* options and sort sometimes not working on table inside a modal ([d29342c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d29342c24d350e97fcd77bf0533f58fbd3f697a2))
* quarters did not show correctly and schedule cannot be updated if it is over midnight ([e5af6cf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e5af6cfafe3e6611b4f7e7d06076753c6bf93a15))
* schedule cleaning history page crash when causer is null ([b3f3c87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b3f3c87b33d506f00449258c600fb99df24c56ff))

## [1.9.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.8.0...v1.9.0) (2024-09-05)


### Features

* differentiate different properties with same address ([bbd8af2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bbd8af2b17b66f355c10876a5161dcad161af069))
* init edit property address ([a508d21](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a508d21ab9d039a9be13d88c8556c56d4d231a68))
* initial add schedule change request history ([bc60362](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bc60362df5c1278ae4a1cb84b7eed0e8d6f6aa8d))


### Bug Fixes

* clear filters not working correctly ([2c085ac](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2c085ac31b23f8770659ed5d55b6f80ceee903ac))
* error when update null note ([1554ae6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1554ae622d5b8d73621e1d1dccf0454beaaa5ea5))
* missing fillable and invalid value when empty ([f60a2cb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f60a2cb6101a2ecaa95b95b7b0b7bb9e71ce6662))
* note error on apps and wrong default value ([1b72417](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1b724175912396aa6ebccdef02b15fb082cbbd7d))
* schedule note is empty when schedule is generated ([b4681ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b4681abb02e9f638843822e864b22a5f17c9a14b))
* wrong logic on credit refund ([d143a36](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d143a360eb57cb20a2e8d6a46e96e74ea41ba6e6))

## [1.8.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.7.0...v1.8.0) (2024-08-28)


### Features

* add 2fa field on customer, employee, and company wizard ([f9b4b34](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f9b4b34d2e6bb133239f0256b1d26ba8dede9750))
* add a way to update in progress schedule ([7725e77](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7725e777233f7fb7ae895c09a62188318fa82565))
* add mail template for sending OTP ([9b384e1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9b384e195390c48d8159a2afc4f604b045dd7879))
* add two factor service and controller ([9e4efc9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9e4efc949ea2abe0a74a19e2dd4782fcb28bfa5c))
* add unapproved hours ([0753a57](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0753a57d129f062af814c935296b687141fd97c4))
* create 2FA frontend and connect it to the backend ([dc4ed56](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc4ed56a5fb2350c5a63c772bda9a30413bf0af8))
* setup two factor authentication ([5a71fb3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a71fb3a33ed1cb7c7c3bfb8c7004cc6237cc6bf))
* show schedule detail when team shown = 1 ([c776419](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c77641952e52e3176a652f11cb916ee7259475c3))


### Bug Fixes

* typo on end at value ([40caf73](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/40caf7353ce9685d798e08f178c78671b1b79a57))
* wrong collision calculation and start at not updated on updating schedule cleaning ([56aa097](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/56aa097fc88c5bfdfb1cac5487fcf43cc77e83c3))

## [1.7.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.6.0...v1.7.0) (2024-08-23)


### Features

* add a new field to filter the schedule by the status ([3dd5be9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3dd5be99130691abe0b9b5878d92d64fb7fc900b))
* add a process tp sync work hour that did not have fortnox attendance id ([ae55e67](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ae55e678e25b5aedf0bb08f7957f0d6857175ec1))
* add button to show team that have bookings also expand the hidden time ([0678a3a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0678a3a86acb69d3494d8d3e4c7b8728b9c7534d))
* add customer column to keyplaces ([ca2e252](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ca2e2526770b8eb137359b968df27078e7f8c8bb))
* allow to create historical leaves ([22c0181](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/22c01819a8e146fd3e2445cd83b6720cfbbcf601))


### Bug Fixes

* attendance panel still showing canceled schedule employee ([0a22880](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a22880869de50d1434ea4d3ebb53bc24fcf6aec))
* canceled worker still shown on worker tab in schedule modal ([184e760](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/184e7607737c6891e71a96f9cdd4fa3c6d64d1da))
* schedule employee become canceled if it's pending when other worker end the job ([59e039a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/59e039a9caae6e2cfb0e103353ff0869bed8fcd2))
* schedule removed from the schedules local state when canceled ([0b91c0c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0b91c0c35e9e0cc378faebc1458ecc1fda07c834))

## [1.6.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.5.4...v1.6.0) (2024-08-19)


### Features

* add command to sync work hours and adjust work hour service ([28d7bb9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/28d7bb98ed91da75a1dc5d4019f45e72b4020660))
* add deviations tab on daily time report view modal ([f12f348](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f12f34802bbc3d35317eba4f4a92a782912ea524))
* bulk change leave registration ([fbd1cf1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fbd1cf1ff5df39086396453ddaf060feb697e51c))
* split customer and employee fortnox ([89eaf79](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/89eaf7988b495f292b1dac66c0fad8f115601391))
* support or clause for the filter ([9badf6b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9badf6ba9372c63d73c6a55c1aa4d4069283942a))


### Bug Fixes

* text not clearly show on dark mode ([269c41f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/269c41f71a3e3fb36e97f1586d6719e5b8950ca5))
* work hour times ([23eda38](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/23eda384b845313dc65c3466d59de786793c7d28))
* wrong filters for getting schedules and employee deviations also not clear translation on the tab ([576f814](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/576f8140b233b7f2adc6c3ca54aa1aa54812e069))

## [1.5.4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.5.3...v1.5.4) (2024-08-02)


### Bug Fixes

* company wizard error if contact person identity number is missing ([4513f85](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4513f859295f9cd4cf3d6d2600c0a48a0f58e66e))

## [1.5.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.5.2...v1.5.3) (2024-08-01)


### Bug Fixes

* monthly time report crash if employee fortnox id is null and schedule employee work hour id not updated ([63e16e7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63e16e7b0f6240b8f16a30ba837c94c31782b997))

## [1.5.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.5.1...v1.5.2) (2024-08-01)


### Bug Fixes

* sync stopped when there is an error ([d6f510a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d6f510a287126b28d7202fa56ba452728142141a))

## [1.5.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.5.0...v1.5.1) (2024-08-01)


### Bug Fixes

* error when assign team to unassign subscription and DTO transformer not working on customer wizards ([349ec63](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/349ec639a8bd4065a3a5f32b2af42fedcaf70f14))
* missing email column on unique rule validation ([02a1a7e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/02a1a7e048d08cf1e5e077dc0656dd0c72ca26aa))
* missing null safety on end at because it can be empty ([e3e8571](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e3e8571bad897f7994298a6ac819b769e6799ea7))
* subscription store schedule data ([26155cd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/26155cd32400ee309304d4d9d45dc7b65bf407a7))
* unable to control scheduler from the outside ([0a3b815](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a3b815b275f7ad851bfd8b7d09b15adc51f812e))
* wrong end at when creating historical schedule ([283fca4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/283fca4de829a1a6c6d97ad13eaa10f54d31e80d))

## [1.5.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.4.0...v1.5.0) (2024-07-29)


### Features

* add fortnox absence transaction resource ([d53cc41](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d53cc414e4a4717c9f8dcba63d0d9c37a766381c))
* add leave registration scheduler ([d965086](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d965086c3f66281c73da796ca5616c5196ef839a))
* add leave registration schedules endpoint ([6bbcc73](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6bbcc734c9d337b3fdcadd48d64f814fb51d02a2))
* add widget reschedule needed ([bf56b5f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bf56b5fbfe26bfc8c8374cc143bfd0d06370954a))
* add widget, test, and update leave registration ([5671ad2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5671ad281ee0634cc1a7ab82c96124a5d3be376e))
* leave registration init ([fcd55eb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fcd55eb9bb5b09c748c442b66bb303d64e1d8ae6))
* leave registration view ([6bc993b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6bc993b66f8fc7a117c88c4f23f07c7fc37e7c05))
* send notification to the customer when they canceled it by themself ([4bfd679](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4bfd6790c2a09bce6c77c4afc6eb5341a7af6805))


### Bug Fixes

* bugs on subscriptions, schedules, and leave registrations ([db42b23](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/db42b23580f777ca678fcc36b0629615e0ec15a9))
* forgot to clear the global settings cache after update ([9792df2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9792df21686904e05b80a76026e35898f50cb9e0))
* UI bugs and missing translations ([baf8b89](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/baf8b89190ef6b57b57ac2363d2ecce416a9311d))
* wrong condition when fetch worker schedules and missing translations ([cf223ca](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cf223caf7c9fd6a406148fc15c4b865e2d7f440d))
* wrong setting key to access the cache and problem on unassigned subscriptions widgets and dragged schedule size ([f916f08](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f916f08c1165f50e9036355d36b9b0d3249f88f9))

## [1.4.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.3.1...v1.4.0) (2024-07-12)


### Features

* add alert start time changes for edit subscriptions ([e560337](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e5603371004a9398c2715f4fce3100cf54b84c13))
* add daily time report details modal ([0e7f8f0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0e7f8f09780193d4f661acf469df91c0a270df3b))
* add pause now action and show warning if no active RUT co-applicant ([a3b066a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a3b066ada073e7a0541d1f2e19ad76919cf6652d))
* add prefill option on company invoice address and fix the forms weird behavior ([4a2ee6b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4a2ee6bafe636ae46df9cb91f36aa8240014b35d))
* add subscription update schedules relation ([45ac2ce](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/45ac2ce6f7a8cc7e9e1af0596b521583909638c8))
* extend rut co applicant ([f0d1b56](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f0d1b56c8750f7c6c53465d7212806f64be56ee8))


### Bug Fixes

* ambiguous column name when the relationship includes a join ([a849d65](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a849d653639044333bd6c35e9c14a320a9363b32))
* missing nullable on product name and description accessor and make refund accessor to comply with eager load ([9e9bb09](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9e9bb0991980925a4207474b281b9184fc2b84ea))
* redis host on pipeline and argument error on relationship and wrong variable on tax reduction ([dc08d79](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc08d79301c42f243669b85612c6eaec973b81bc))
* trait unable to process accessor that returns array ([bd4c996](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bd4c9967d2ac141e050441128f46fb766f67aba1))

## [1.3.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.3.0...v1.3.1) (2024-07-03)


### Bug Fixes

* model update always itself because of missing fillable on last seen ([95f3064](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/95f3064c7420c27416c1d19768805d06bb578e52))

## [1.3.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.2.0...v1.3.0) (2024-07-03)


### Features

* add a validation to check if requested column is exists in schema ([26884a9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/26884a9ba014fae4109cffb18c73a9573230764f))
* add a way to resolve relations based on only query string ([0e3e113](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0e3e1132ce6a02e75f0cda0d63d9c9a66598094c))
* add automatic resolving relationship based on only fields ([a239a06](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a239a06cadd003bc8dcd2bf72aacdbb53ba6c041))
* add index on columns and remove unused react pages ([c5580bd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c5580bd26d53565c588c5016acfa0abdd24e6c15))
* add resolver for polymorphic relationship ([0edd7e6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0edd7e626325fa071b9069c5072fb49f5b3018ff))
* add resolver for relationship on meta table ([53ea521](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/53ea521ae89bfa4038f80571bbe3474af1601959))
* add selectWithRelations method for easier optimization ([e9bec3c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e9bec3c1d3eddb5a041f831ba551fd3b37a1f4ca))
* add sort on columns, adjust seeders and fix credit service ([1cfe9dd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1cfe9dd8bef0f821b95796a2ffde0ec19991bff6))
* add unassign subscription page ([5e6a1f6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5e6a1f6d891e5239d1478956096b8f420c8c4369))
* cache user data authenticated by sanctum ([7e7ab10](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7e7ab10d6e2e4a4d93574da84603c197e62b6fdc))
* handle hasMany and morphMany properly ([d58ffc9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d58ffc93d40e84f00dd71a4876f9a00d05242210))
* initiate unassign subscription table ([81c3ead](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/81c3ead36e0d651e19601791b38b79953a2670a7))
* optimize get translation query ([1869d7a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1869d7a37d6af81ed6f74f5820dc3a6e6c0aed53))
* optimize queries on middlewares, auth providers, and schedule page ([ef158d0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef158d0ff47effcf901c8bad6c227c54c936eb44))
* optimize query and fix invoice ([b3284e9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b3284e91a6c8eea508bea9f5912a6174b888ba2e))
* private unassign subscription ([a08559e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a08559e29f95bf5a0e62c2714c4351317b290b39))
* remove unused API endpoints and optimize some endpoints ([0008177](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0008177417bf5b8fef235aa7208e5469060a555f))


### Bug Fixes

* bugs after optimization and bugs on unassign subscription ([09764fa](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/09764fa19f8b05d9117241a95f6ec3c3aa589e96))
* bugs and performance issue on dashboard and issue on schedule page UI ([b401d98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b401d984999e265388aaef7a7744d88292edf378))
* cache not cleared on company users and error message not shown on unassign subscriptions ([2d07927](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2d07927643fd4cd261d3e70977d1e75b55d5945e))
* deleted employee did not show on the schedule workers even if the status is done ([097faea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/097faeaa681990cc13bf792712db48d96c991b40))
* duplicate selected fields and wrong query on getting allEmployees ([583dbc5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/583dbc51fc0859aca68d890991e61f32d7531152))
* missing ordered_at when creating order from old DB ([3209703](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/32097034c98289f1f58c82fa1756d473fee35206))
* notifications translations ([9d8690b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d8690bc59139541af8aa63137b01aeb46fa4c69))
* schedule page stretched when showing too much teams ([8a3eb15](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8a3eb15dbb7c353ff7db430439e8f57d61c00a1c))
* schedule_cleaning log channel is not registered ([a42cf02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a42cf02faf6d2a94baf7589cef3542cad58a55e4))
* translations failed to fetch and user cache not updated ([86f7af7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/86f7af74c76fc6b4c9271d37640bb12dcd9b1383))
* unhandled hasmany/morphmany after polymorph ([40ad459](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/40ad459f7c51f093afbc373bb76fc534047d799f))

## [1.2.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.1.0...v1.2.0) (2024-06-07)


### Features

* add command to handle all kind of deviations and adjust deviations controller ([f4d3bbd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f4d3bbde08572bd8678652a85c9963c76e6a0d88))
* hide hasRut field when selected service type is company ([44ce158](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/44ce1580e47c7cae5e2f6633247cca7d5e53ed50))
* trigger health check when visiting dashboard ([3b56488](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3b56488261e979760098a6c0934afca976c80f4c))
* update invoice ([f1dcfcc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f1dcfcc64e083f71293b1fe578ced1912e2cccc0))


### Bug Fixes

* invoice and UI issues ([ee0106e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee0106efde43ec3d4e74829639851c7516e26635))
* use dispatchSync on non-HTTP context ([a1b29bf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a1b29bfa9389be8402a7739380994907d7903b41))

## [1.1.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v1.0.0...v1.1.0) (2024-06-05)


### Features

* monthly work hour ([8c3e1d7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8c3e1d7fdc97f9bdae29f77f4080d3cd8ccf9527))


### Bug Fixes

* datetime on notification content follow the wrong timezone ([631b145](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/631b1452cc2537c40951fa441834d76aa14c07b5))
* displayed date or time on notification content is not adjusted with user timezone ([5b6e397](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5b6e397afad3fe1ac87d3e6380e9209bb3972bde))
* schedule placeholder not following target element width ([6ffcece](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6ffcece49cbd360fc2a053a53003cf72d5b7b427))
* unable to edit primary address ([fb42a7b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fb42a7bc37538a423fa7a6e8cf74e2ed54d99689))
* unable to find available workers if schedule has no workers ([0687da9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0687da95df44acfa0dd401da560a3c3a37816700))

## [1.0.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.21.0...v1.0.0) (2024-05-31)


### ⚠ BREAKING CHANGES

* add API endpoint for work hour

### Features

* add API endpoint for work hour ([bf5b126](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bf5b126dd24b7991f34a88c126c3c732a88caa84))
* add booking date and order id on each order rows in invoice ([e038c5a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e038c5ab13a5a319248400f18ade693231f1ccb0))
* add service status to dashboard ([e4799b1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e4799b16a8bb3c2b84175649a99453c636734592))
* adjust business logic for work hour ([5acc9c0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5acc9c0fc211a4846f23ab92d2ba95b8bb199400))
* create Time Reports page ([f686b15](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f686b15183645af32daebddce9e44e3ec8402167))
* pass servicesStatus to dashboard page ([67baff9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/67baff98b24e36be8d24cba960fbc9f2b1caa194))
* set up order at field ([8845d93](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8845d93e691745337ad9c833031c12cc55f31673))


### Bug Fixes

* missing name from product summary response ([9aad544](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9aad54485e3283d79f22f28b63f15249c4110ac1))

## [0.21.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.20.1...v0.21.0) (2024-05-29)


### Features

* add check for collided workers ([aa44ccb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aa44ccbb15baa18f2da958501e6d7543acbcd7e6))
* backend for update add on subscription ([5d8c119](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d8c1199c83ff28229fa34976278dd83f85418fa))


### Bug Fixes

* collision on enabling worker and server sent object instead of array on scheduleCollidedWorkers property ([860583e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/860583e2d4e000daaa0f15a35c6fc836a2514d01))
* compare calculatedPrice with totalPrice before submit ([c9c3ac4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c9c3ac4c0a29d7711da218216f8cbe30d8847f5f))
* fix empty value totalPrice and error handleSubmit ([98e4bde](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/98e4bdeabf8bf126c7cb8de6655b3d6c46b46fe9))
* subscription total price miscalculation, invalid schedule filter, invalid alert message and column name ([0c24baf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0c24baf9597d9eaef8cd8c0e93aeb93fbeea4ac0))
* update attendance is not a redirect response and edit invoice button is not hidden when status is not open ([83fd519](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/83fd5199b223cb45911580a215d3e7b1287ab7f9))
* update subscription model bugs ([c76ade2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c76ade2a1b0fcc7a018e9533fc45b435618f4091))

## [0.20.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.20.0...v0.20.1) (2024-05-17)


### Bug Fixes

* cell not render correctly on day view ([783ab17](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/783ab17d44705051ceb8db47e082c7947bb1deb2))
* change schedule block color for started or ended late ([0d09c69](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0d09c697c7535af124ddafeb6dcee26566bdb7de))

## [0.20.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.19.0...v0.20.0) (2024-05-17)


### Features

* add global setting for default team and hours to show in schedule page ([26fbdd9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/26fbdd954523feddc2de2642ad36f20f260aa492))
* add message to success response and function to check if response should be json ([26a1b79](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/26a1b79a24eb383a7731200044593c0716d4da15))
* add rut co apllicant tab ([a54a9a0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a54a9a0a71f06a376636f161531faf1839b5a88e))
* add rut co applicant backend and migration ([937b9b8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/937b9b8e267cf39aa6b7eb05f1f4ec0b4385c6cd))
* add shortcut button to select all subscriptions and condensed options multiple select ([fa199ba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa199baea8296713f554a0cee97eef72c1f7b00f))
* adjust the value field on edit form and rendered value on table cell ([c4b72c2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c4b72c2620c0fd1845edc6087cb7348c2f556a58))
* change wording from Cancel to Close on modals ([d7f56b2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d7f56b2af216ef60271038dbaef8d9562b1b4236))
* use json response to optimize schedule response ([bf588d1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bf588d1a46a9a3e3bd851030c21c379b573bfcb6))


### Bug Fixes

* base url for web client not referring to root url ([f350560](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f350560a728c524bd8962136a18e0dbb9c770400))
* error cancel schedule on old subscription ([f5eeb25](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f5eeb2551387186eae054da7cf26d8fcaf36dc2d))
* invoice fixed price laundry customer type ([4be5926](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4be59269e96c052cd101af0e72e61c725e93c770))
* schedule issues and design flaws ([265b5d1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/265b5d1116a6515d2f104a427af06d4c60a98405))

## [0.19.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.18.0...v0.19.0) (2024-05-14)


### Features

* add a placeholder tags on employee wizard role field ([c3c9f05](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c3c9f05a4558237aba4857fbafc632c957028e6d))
* add schedule cleaning task ([98ad731](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/98ad731c3b50a3c3796ebfdd7737fa20f1bcf136))
* add UI for task localization ([2886469](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/28864699d95f2255cfd237f235d11ff768365418))
* improvement block days view ([daa4fc8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/daa4fc83e1a4ad94108c2bb006380623cf3a5f46))
* optimize create schedule from subscription ([676b94d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/676b94d2fcb9716b7ac0c611c125ba07ebc9ab84))
* separating task form into SV and EN ([d3e4ba3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d3e4ba36629482ace9f114b6084a730d7229c78e))
* sync permissions and global settings on start ([3919c43](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3919c43ef4915c7bf6602fda82aa86731b3ae303))


### Bug Fixes

* automatic scrolling on sidebar active menu item not on correct position ([e74ae01](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e74ae01fcd839c2382ea9c16a39dfac80d468ece))
* crash when there is no translations ([81e2c6c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/81e2c6cc7e4b4bf3cd7424281ce1ee0b58dafc5d))
* error when access user on deleted subscription ([de273e5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de273e5f6cdf8992adbf091037a6305d3e6426f5))
* filter not working, error text not showing, and crash because of soft delete ([9206d6f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9206d6ff4488cba30f80d96ad7a31ad156b209b3))

## [0.18.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.17.0...v0.18.0) (2024-05-06)


### Features

* add attendance record panel on order view ([1c6c750](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1c6c7506b28fda668045e0f70c6d4eaa5634ab83))
* add function to broadcast notifications ([4a15266](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4a152667006f00cc95a7b9e82073e3653a8eb648))
* add send notif in update setting ([b6227d7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b6227d7a9a63663dcb05a9b4685c804213f24389))
* set datetime property on API response to Europe/Stockholm timezone ([c060a7b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c060a7b25dcdf4170f49df6dee2c8f00b433b2b1))
* set localize for send setting broadcast notif ([75fb5fc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/75fb5fc25aa767cb3f00fe78b619eadb1385ebf8))


### Bug Fixes

* select user type modal when successfully create historical schedule not dismissed ([4e16e6e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4e16e6ec326dee8b076d337c526c9930050819ad))
* unable to delete resources, wrong behavior when filtering with datetime on api, wrong date group on user schedules ([d45a73d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d45a73dcc95464e40849cb94c91a3126c278ab19))

## [0.17.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.16.0...v0.17.0) (2024-04-29)


### Features

* add schedule historical wizard ([9eac829](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9eac829aa21b7457821edee180ff8ea65b0413e4))
* backend schedule history ([668a74e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/668a74ee55cb85c42cd0f5e26e1ce3f543fe9bf8))

## [0.16.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.15.0...v0.16.0) (2024-04-19)


### Features

* add remove action to remove schedule worker ([db84b59](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/db84b59218018df4e614a776fc133ae417a8d9e5))
* change success sv translation ([dcd82a0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dcd82a01ba18d93aa9586e8418b16103f1071ae6))
* improving some Swedish translations ([bcfb47d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bcfb47d85c3267a77b683c3f9e40a8e7448a1e7f))
* improving SV translations and relocate modal buttons to bottom right ([4909a71](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4909a7136caab0e68817c1aa294ff17aad64a61e))
* improving wizard words translation on sidebar menu ([d060aab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d060aabbbb0f4f1960fcedfb26e5e195070c967e))
* move button position on modal to bottom right ([7d49bbf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7d49bbf6a36a58d0c5aab1da6543a9599d86d9ae))
* prioritize schedule generation queue to reduce time waiting ([63ccba2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63ccba240a436b99b5ebf9f2aa87d9ab903e96cd))


### Bug Fixes

* account link to fixed price and discount not working ([ec6bff0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ec6bff024feff502b03cee40786879df20e16ee1))
* account link to fixed price and discount not working ([f405407](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f405407686993a98e1bd1b5268550079fe3c0822))
* add validation in some places ([546adea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/546adead0813c4c7b7d826c637af43f1b07c8096))
* notification hub not pointing to correct environment ([3cceefc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3cceefc8281251d50fe3fc68b018db20cce87d51))
* notification hub not pointing to correct environment ([a270079](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a270079620ac71d70a94752613a6160fc29e547b))
* overflow schedule block, cached change request widget, and missing translation deviation ([367a11f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/367a11f7018507cfabddbe72b498b9d71fde4c13))
* queues not working ([1e72835](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1e7283553be293fc3412d304cbeaea585292384a))
* remove weekday and change to accessor ([6d281ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6d281abbc71350e1a5e4255be6dbaca6c35290ca))
* Resolve merge conflict ([82c94dd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/82c94dd73cd6540cac34c1100fce278372def886))
* schedule panel date filter not correct and cancel confirmation modal not streamlined ([e93da1d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e93da1d0d555ba9ce7e3b349e7fe618ada1ef3cf))

## [0.15.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.14.0...v0.15.0) (2024-04-05)


### Features

* add reschedule without notification and fix discount bug ([62e5015](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/62e5015a6c9f368f14fd51dcf5bb2396463f9139))
* associate fixed price and discount to user ([8741fde](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8741fdec70f33d6b705bbd69f30bb2f1aa4ff87e))
* remove apply to all subscriptions in fixed prices ([382aab2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/382aab29d5daa97bfaaf215cd7458967347f4ed8))
* remove canceled order without credit in invoice row and fixes bugs ([92774f5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/92774f5ac141345c2a1888ba5d5e71e029f79c9b))


### Bug Fixes

* fixed price not showing credited text in invoice row ([4e24276](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4e24276f9811f79e4cad519c295889c6b1aeb8e7))
* missing fortnox_article_id on order row ([ebd3c6d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ebd3c6d9b8c65728f1f27e71fac2520ac903e32f))

## [0.14.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.13.0...v0.14.0) (2024-03-28)


### Features

* add auto crete fixed price ([a0231e0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a0231e0afd96ec80ef5d1909de3d30a10a9c0181))
* add credit panel for private and company customers ([3f69a9d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3f69a9de7176c681c360eff61d9c09956ac98f32))
* add new card for total valid credit on dashboard page ([189aa4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/189aa4daa5f067e77ab0605509b2d44c8b8f0d30))
* change credit words in dashboard ([c8a8d02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8a8d02834b266746325fc372c669f662a35cfa7))
* Create command for solve not started deviation ([5f7c698](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5f7c69856cda6400f689acfa418d23fda5585ec1))


### Bug Fixes

* missing translation and wrong behavior on total credits ([9a5990b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9a5990bf856098bef0b6bdf4e01ec4454c10282b))

## [0.13.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.12.0...v0.13.0) (2024-03-22)


### Features

* add a way to refund when cancel schedule in portal ([ca3ad0c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ca3ad0c1b9f2c8eb884ded7bc84f2f3f0569d9b8))
* add created at column in employee deviation ([19753c0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/19753c0fbac5d9ce6145f58b280544e0aeb49f60))
* add refund information on schedule cleaning response ([94f69d1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/94f69d1faf74ec4bc12befa3565bf9503d8fe8da))
* create business logic for credit with individual expiration date ([11cfeb4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/11cfeb494d74eea91ecc59a1a1f02e208923d482))
* set up credit migration and model ([9597eca](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9597eca47a8e0cdb27f9e739c31e5b9ee9cab17f))
* update dto rules and controller ([e9a2e80](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e9a2e809866939bf3ff9029edd42e62db1d9b065))
* update worker notification ([4385bfd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4385bfd52593df669796a0bb18c590f327815b02))

## [0.12.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.11.0...v0.12.0) (2024-03-15)


### Features

* add a way to cherry-pick the order rows that will be overwritten by fixed price ([2705443](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2705443f2bea94e91d98e6d22e62d52dde518559))
* add finished early deviation ([372ef29](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/372ef29ea03ca1698f7df4e86cad8781d7da7ead))
* add tax reduxtion fortnox ([29322b7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/29322b7a6eeba0c451af8fbb2b62c562766fc3cc))
* make modal remember the last active tab ([dbd8873](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dbd8873cd1812db3f22ec04a14bd39497caee3ca))
* Setup invoice table and model ([cacb6ab](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cacb6abfaf0d843ea8430519999e0615ea262fca))
* update pause and continue subscription ([00843f7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/00843f784468f021884f50b3f25aeadb7bb46dd4))


### Bug Fixes

* order with status not draft still editable and rows not editable even for order without fixed price ([39bd63a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/39bd63aa7e854d2b3ef5c23ffd235efa6edcf9e9))
* remove deviation on canceled schedule and wrong company redirection ([c41c91d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c41c91db72abe25f2344b96c728f8ad17028b125))

## [0.11.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.10.1...v0.11.0) (2024-03-06)


### Features

* add no early handle validation in schedule deviation unless attendances are complete ([73503b5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/73503b5fbc796ea8d8826d92e1afe2c7a7787968))
* add swedish translations in schedule deviation ([1040bde](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1040bdef9dba0160d8cefe5d92eff586f97ca912))


### Bug Fixes

* order detail error on old database order ([31bc796](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/31bc796624cb6ef4e0a03fa85c20caefa7345e06))
* remove unused code ([58699ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/58699ffc3a5c5d1bd1646efa5e66d65a5c7b62a7))

## [0.10.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.10.0...v0.10.1) (2024-03-01)


### Bug Fixes

* total work time accessor not in seconds ([dfdff65](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dfdff654ddd4db392f0c095a344edf2884f5b69a))

## [0.10.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.9.0...v0.10.0) (2024-03-01)


### Features

* add apply per order ([5d7f4f1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d7f4f1e8a28bfb8e7d489f6e22941795650a4ff))
* add include laundry in fixed price ([b1ae9c7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b1ae9c76d3d4ec83c446655baca869335e90636e))


### Bug Fixes

* order created even if schedule has deviation and miscalculation of work hour ([234f5ba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/234f5bab7cd7fc56f9ad35827e72e0d22a7c85d2))

## [0.9.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.8.0...v0.9.0) (2024-02-27)


### Features

* add edit worker attendance on schedule and employee deviations ([ee781c1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee781c1845d4124af5d595755aaace900b3d3630))
* add week of the year in schedule ([30353ce](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/30353ce60e8663caeb9c333cb23a32d297e198d1))
* cover cases for incomplete attendance when handle deviation ([7532462](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7532462e23c152525221786d9e3fbc5c2235dfb2))
* persist the filter and team changes ([79d586b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/79d586b1bcc998f7cbf1a6deb0663d027469356b))
* schedule blank clicks redirections ([6196eba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6196eba3bc76c66abfae6225e01b76c244fab18e))


### Bug Fixes

* can not start past schedule ([3bb5d1e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3bb5d1e087be3043d02e007fc7d5372c78936638))
* incorrect format because of array_diff ([11ccd92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/11ccd92e362bcd4097ae5e599ca2e3055d6b92a8))
* no deviation for not started worker and work hours still created in fortnox even if the schedule has deviation ([9b2694a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9b2694a77c24978bee731d14ce58bf73e283782f))
* schedule cleaning status not updated for not started deviation ([80ccb9b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/80ccb9bec556ac0bacee810ea93ee4c7ef2449fe))
* wrong hour calculation and missing field in model fillable ([19e9e32](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/19e9e320033e3451dee9cc53f98945a841fc32a9))

## [0.8.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.7.1...v0.8.0) (2024-02-22)


### Features

* add schedule deviatoin not started ([dd7bab0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dd7bab06d4a5cf2a7b82f94c7603f3a6c71524ab))
* show collided schedules on update team workers ([506e550](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/506e5504630f4b138ddbfeb71759a051c25650da))

## [0.7.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.7.0...v0.7.1) (2024-02-21)


### Bug Fixes

* migration script not run on production ([6bcfd75](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6bcfd759704144ae744ee9b268b97847954f6c6d))

## [0.7.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.6.0...v0.7.0) (2024-02-20)


### Features

* add due days in customer ([877b2d3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/877b2d3a52eb607734286410705221755ddf564f))
* add due days in primary and invoice address customer ([4b101fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4b101febd2014c9ae37842e6dcbf30933035cc74))
* add due days in wizard ([48140a9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/48140a90454d5138f8572e8d50333a9d4054e6a3))
* add fortnox log ([2025e57](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2025e5750bc23baa90fcdedc92a703a6a2ef06ac))
* workers on existing schedules can be changed when team members updated ([d7b68f8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d7b68f82387c42dd2ae02f2cc3b120021dfb603c))


### Bug Fixes

* schedule end at not updated when team workers changed ([37380e9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/37380e9f523cee844648e6442a779fa45da44a55))

## [0.6.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.5.0...v0.6.0) (2024-02-16)


### Features

* add a way to sync existing data to fortnox ([#254](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/254)) ([bb16c20](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bb16c20862552bab618711a3180de525f5775201))


### Bug Fixes

* language not fixed to sv_SE when sync Fortnox ([#254](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/254)) ([8955d59](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8955d5945f984bf5cf95799353194d3e15a8548a))
* misleading label and add missing localities ([da1cd84](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da1cd84d7821bd3d3ff81a696d2e55645a7cc34d))
* schedule on sunday that continued to monday is not showing ([329d5f8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/329d5f86e8813ac4a61fecf45ed3dad3a904b56f))
* update typo ([897f3c3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/897f3c36643b94bf5a19dc20254fd6028325120d))

## [0.5.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.4.0...v0.5.0) (2024-02-13)


### Features

* add work status and deviation indicator to the schedule view ([e632500](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e632500367694c25a1f36998205d6b64617f5550))


### Bug Fixes

* bugfic curreny filter ([e6335af](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e6335af5cc3e8ed0da4501c9588ff0155bf404a3))
* company property type ([7261727](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7261727c303c1bd1e3637583fd038e449bbce777))
* fix customer overview fixprice url redirect ([7e4f3d3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7e4f3d30e751bc2116d8f2762bfb19ea8486f8d5))
* fixed price filters not working ([8bbb084](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8bbb0843bb73ca2d332645bf7969ee6ce7e21672))
* not working filters ([4ab00a5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4ab00a504d2e4fc2f7d9db7b53bfb2f29b3735c4))
* property type filter not working ([b9d664c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b9d664ce1633fd31be2f1029f70efc1af1a40a31))
* unable to handle deviation when subscription/schedule deleted ([5d4b0e5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d4b0e5544d9fcd99ae37dd4258dd7d2163fdb31))
* validation error on once subscription that over night ([d2bb29a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d2bb29ac01f1578d5fa02942cfb31922bb30ca2c))

## [0.4.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.3.1...v0.4.0) (2024-02-07)


### Features

* add a way to filter simple accessor ([3ddf65c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ddf65c24fa76405189428e662a6a3dbf4a5a567))


### Bug Fixes

* failed to handle deviation when schedule/subscription deleted ([8bff5ad](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8bff5ad25c2f76ea4c59d7bea2ce8d7471932d43))
* UI and behavior of schedule in schedule page ([524e8ac](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/524e8ac2d70391b3cda1f3b23d0802a190650c66))
* validation error on frequency once at early morning ([ab295c8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ab295c84941b4b92d04da5a26c5abacb8fc298d8))

## [0.3.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.3.0...v0.3.1) (2024-02-05)


### Bug Fixes

* week start still on monday after login and early monday morning schedule not showing up ([a3a6321](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a3a63216d0115b749fe2080a8ee7ebbc854ab795))

## [0.3.0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.2.4...v0.3.0) (2024-02-02)


### Features

* add default function filter phone data table ([2f50f57](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2f50f5737c631e16c5b987689eff03ce3ae05d25))
* show collided schedules on subscription collision error ([3917f35](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3917f353a32be8cc4fc9f30892ffbf78e7a1e375))


### Bug Fixes

* customer address and property validation ([a909fe9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a909fe9dd80ef74d448b364b9d1bb1ea9311bc8e))
* datatable recreated every render, zero value omitted on filter, request leak, etc ([4132d16](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4132d168389de5f57269bd72e467461499756756))
* fix pointing url and translation delete company discount ([ca7541f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ca7541f627d60139b9483153ccc945ee3cc87d8c))
* query with null value is not ignored ([dc407d3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc407d3d78df90aeffd7e9048704c33d308e6836))
* status column cannot be filtered on customer fixed price ([5d45bb3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d45bb3f104e197dd6aab63925a843ade28ca901))
* wrong start date when time less than 1 AM and no indicator on collided schedule ([e499f2d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e499f2dcfb2f7c477718c1e80399a13348b7b482))

## [0.2.4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.2.3...v0.2.4) (2024-01-26)


### Bug Fixes

* scheduler command typo ([a583573](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a583573ed59bb8e10530a545936a9905c13529fc))

## [0.2.3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.2.2...v0.2.3) (2024-01-22)


### Bug Fixes

* schedule store not resetted, calendar quarter zero division, and deleted subscription not handled ([bee44f8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bee44f8aaa4abc49bc3a8f641110f8ea424c7536))

## [0.2.2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.2.1...v0.2.2) (2024-01-19)


### Bug Fixes

* action button not visible on dark mode and add space on the bottom for scrollbar ([c427bc3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c427bc3ebeb603212f9d809febd1db9f3478a65f))
* alert message, filter value, update employee bugs and missing cache to remove ([2ecd232](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2ecd2327c0c77046be6eda2982ead730d0d00fc8))
* logo url in email template ([ae2a548](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ae2a5481f3ae991fb68a4beebeb58fc536053ad2))
* user filter not working on authentication log ([ae9af41](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ae9af414527867b5573648225cdc9912fb5ddafc))

## [0.2.1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/compare/v0.2.0...v0.2.1) (2024-01-17)


### Bug Fixes

* autocomplete option text too long and fortnox error on update employee ([036bb4c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/036bb4c57da20276257596ca19f823a74596c7ed))

## 0.2.0 (2024-01-15)


### Features

* add alert component and fix path aliases ([58769e9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/58769e95dcb794308d7ac5a94db2c33d71f0feed))
* add alert component and fix path aliases ([22853a7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/22853a7844582e27e341df06ff9506b0db71a56c))
* add attendance transaction resource to Fortnox service ([2c27422](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2c27422a1f4dd3d71d8c9303ee21b1d14369397e))
* add Azure Notification Hubs service and group the Azure services ([#137](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/137)) ([82b57b3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/82b57b3458af61500baa8b7b1f38746325d1985b))
* add Azure Notification Hubs service and group the Azure services ([#137](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/137)) ([8aafacf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8aafacf08afe90d4eb9768f227f5ecbd66312899))
* add backup log scheduler and create send notification endpoint instead ([dc7a4c1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc7a4c1f000875907d2de723c7a4a693cf5d022a))
* add backup log scheduler and create send notification endpoint instead ([8a29b2f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8a29b2f3f5bacbb4076114cdbb24118571e51922))
* add backup logs command ([17d2433](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/17d2433b37e7a96b805087023eefef8bba23caec))
* add backup logs command ([0cbf791](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0cbf791b020d914ac718a518e97b9766b4563e0b))
* add breadcrumb and fix pagination bug ([65c8a12](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/65c8a1231837d4a422629972956d7bf430664661))
* add breadcrumb and fix pagination bug ([e4369fc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e4369fc9b46e54fe492fc6a88a50d98cb63542ed))
* add cacel  customer schedule tab ([7028d53](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7028d53a9763f44b7b00e0df74dc8a67a785d074))
* add cache middleware and fix fallback locale ([4ff705c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4ff705c255254bb810372a4852f9df88fe328481))
* add cache middleware and fix fallback locale ([ce11607](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ce11607c41c9420f09d940ad574a0d6af5f43cf4))
* add cell formatting in datatable ([345efa7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/345efa7b4e93dcd9266289bc9c5135e938c81479))
* add cell formatting in datatable ([f679722](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f679722157dadb8353ebd149d45fbc94a4b8132a))
* add change request and schedule modal ([1f539da](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1f539daa02bdf91c8faccf913570df6c369da7ae))
* add change request and schedule modal ([1903257](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/19032579184bd62ec9803e0cb62655410034daf0))
* add change requests and deviations json endpoint, fix some bugs and missing translations ([23d330d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/23d330d90d4d351f2182d26c006eeff632cc3245))
* add change requests and deviations json endpoint, fix some bugs and missing translations ([6248182](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/62481823bf6fac972ae8836335e096fbec3d6dfc))
* add check subscription collision endpoint ([bbb1da5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bbb1da51bde87d018fd59cf9ed397045a0e919d9))
* add checkout endpoint ([75ac144](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/75ac144237bd7fe6072175b1429b7cf21b347347))
* add checkout endpoint ([eec64d4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eec64d4673081130761a1df75837d3f0361e754a))
* add city migration and geoapify ([092b5a9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/092b5a90da5a5cc7e3a74e6d673cd476b694763a))
* add city migration and geoapify ([af4525f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/af4525fbb8db793c8378f779553d952d552d9f61))
* add command and service for migration ([#202](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/202)) ([6b4eaea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6b4eaeab1995e7d751cf80fdbebde690bb35c49f))
* add company customer ([120e8ba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/120e8ba4b345231d577ce1e9659b0715b1018f60))
* add company subscription menu ([deca7c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/deca7c9d65a98bd34698052e1a61438abfad1d04))
* add credit and use policy ([#148](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/148)) ([7db403e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7db403e0ff7150e5f3b82390ec8361d3dcbaf06e))
* add credit and use policy ([#148](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/148)) ([c3ddb52](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c3ddb52e0cd683c47a4b2250b7299734be2cef65))
* add customer discount migration and model ([e4b2ba4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e4b2ba44816c63d6b1bfa93c10d53ad3afef2cce))
* add customer filter ([6621fe2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6621fe276001bdb21e8ec3bc016c91ef4336be74))
* add customer name on day view and show when hover on week view ([d682cfb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d682cfb7ad5183a93317cca875e2d87daa38182e))
* add customer wizard and some components ([870c864](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/870c86456296c4d1de04873269487dc243a6571b))
* add customer wizard and some components ([ef8f728](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef8f7289e42332bb2bf90b35de352f043f34f78c))
* add database transaction in all flow ([#98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/98)) ([0a37a26](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a37a26756e4527225a0a401b186046a97321f81))
* add database transaction in all flow ([#98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/98)) ([2737207](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2737207f1cdbf78ff00bfd375fcacf0472615f70))
* add edit primary address and invoice address CRUD ([6373792](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63737928ad20b595f0e99613cb22b83a0f461478))
* add edit schedule and add schedule task ([5b8612e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5b8612e050cc900e1d2f87bf99c5faca1c0f7f2e))
* add edit schedule and add schedule task ([8345d92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8345d92630d385378e3238805e180d0cefc74931))
* add edit subscription and apply scoped localization ([#208](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/208)) ([34f46cd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/34f46cdcf3e69c4fb2956b31a9bc4fc712d6713a))
* add employee deviations widget dashboard ([b27462a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b27462ab40ee84cc045778e8363867dfca02126c))
* add enable/disable worker functionality ([2223332](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/22233327557d2620f177c6e03b4dc8ee6c78017a))
* add enable/disable worker functionality ([0f07fe6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0f07fe6ec5c9ba260267039613600ad0adcbfca4))
* add ETag middleware and base model ([a037a16](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a037a16c47215373da43b41a6b654c2874a0d9ba))
* add ETag middleware and base model ([dfd9729](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dfd9729b1f76754ba836c40531eb6c93422709d4))
* add event listener in start and end job ([6873121](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6873121ae1acac363c9aec164987f6d71979f8ef))
* add event listener in start and end job ([0565d02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0565d02944c36052f8153cf17b906aa76ee1bab9))
* add exact as a filter mode ([eaf5fdd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eaf5fddde1056f93871f3ed397b64cbd149fbf24))
* add exact as a filter mode ([bb194fd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bb194fd8d5c3370e39d58f9f2ea1fbc6b29d3fb9))
* add feedback feature ([9be89ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9be89ff02bc2104c4bad6740f330785ba7707699))
* add feedback feature ([2ac0550](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2ac055011a6f0d56ae4005c292490322c4b12846))
* add find schedule availability ([9d3da75](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d3da75ba74e8ba8cde61a4ffcdfb31d66cdfaf7))
* add find schedule availability ([7e0b0b3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7e0b0b3f824913b9f6542c0aded4e3b475d9e1f2))
* add FortnoxService and refactor blob storage and notification hub services ([f8ba783](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f8ba78334fe061916ed69872587e53d45b144262))
* add FortnoxService and refactor blob storage and notification hub services ([d1a927b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d1a927bbebae3c912fce0658187758f32e1e95b6))
* add invoice address, roles in sidebar, and boolean fiter ([b05f0bd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b05f0bd079ee76e28e9e4e347c1c8875c4e0da43))
* add invoice address, roles in sidebar, and boolean fiter ([f5cd1c3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f5cd1c3cfed39a95c65e981dc73c796c05bb0119))
* add key information in property ([a2f0ff1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a2f0ff163fd54cf350198539250cda216ce2075c))
* add key information in property ([c8bea26](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8bea26f13a5aac8295d8c8d13586c9aa982de44))
* add key place ([5cd590c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5cd590cde4af2a0068bdc6b932654dbcecfecdbc))
* add key place overview ([ede8ea4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ede8ea440ef3b539514e154d7f0457efcb909942))
* add languange for sv_SE and nn_NO ([#159](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/159)) ([18ecf81](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/18ecf8109675f22e03ce9d8a643a04762dd34464))
* add languange for sv_SE and nn_NO ([#159](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/159)) ([2cd4ba6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2cd4ba68316aeed1a12de5479bd10c98a9f294c0))
* add livewire ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([581be6f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/581be6fe1b4002614174f5294d7069be0bd6d5c8))
* add livewire ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([4cf40f1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4cf40f1eb4942e975165cf2fa354b5553c5ea751))
* add log in scheduler ([685e11c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/685e11ca4a46e79ea27ac737aed3ba28e2bd1a78))
* add log in scheduler ([327f69a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/327f69a0c9930c9c78158eb472ac9ac0487d1206))
* add main layout ([6cbde9f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6cbde9f01a7d8e21a69ecfb165391bd00f5b58d5))
* add main layout ([ab67e94](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ab67e94844e4f0ccdb4ad29189afaf582ab535e2))
* add modal create to navigate to wizard at schedule ([ff0c305](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ff0c30550350d9e0dd287d68cf639493e9ed6973))
* add notification mock endpoint ([015dad1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/015dad16729804965b70ed8249d853fbbc470eb0))
* add notification mock endpoint ([39248e5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/39248e56f4b63ac55f77245dbd8112134d03dffa))
* add number range as new kind of filter ([49b6fee](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/49b6feebf09ed1b87a9a308875bd314e044a62fa))
* add on task ([#190](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/190)) ([01ed1e4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/01ed1e4a8823eccd83e3a8b5aa785f26f4bd438d))
* add on task ([#190](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/190)) ([b449e03](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b449e0372eadccc2fdb4252caaa91c0073d9dc1d))
* add order and invoice menu ([de5a5f2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de5a5f2fee4b2e2ac05833fc3054cc45f72a6fb6))
* add order and invoice menu ([3e7c7c1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3e7c7c1e1414c137435581764260d208879c969e))
* add order and order detail controller ([#95](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/95)) ([eab2cb8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eab2cb89aab02bab907753d5f2555d9be6cfaabd))
* add order and order detail controller ([#95](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/95)) ([9597670](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/95976700fe81b9d19cbdf996fb49fb1e1486b502))
* add order API and test ([#95](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/95)) ([415be93](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/415be93f0d847e7a228291bc8df89e03ffc49881))
* add order API and test ([#95](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/95)) ([d33d91f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d33d91ff197a03891bcab532e21481db1fe1b18b))
* add otp ([bb626cd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bb626cdc4b87afbeb26c99e483c9ec35adf36b52))
* add otp ([b4b83cc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b4b83ccfbedd03403e6823478ef5518f08b95017))
* add pause/continue action for subscription ([ebd13fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ebd13fe6f1c250104d69c90f51f120a65a111eb3))
* add phone input components ([284b6e8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/284b6e88815fc447b2e3ed02aca0ecab9b67de6e))
* add ping method for health check ([ee2e129](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee2e12946912315e93b7ac3d4174f3a9f7ed3613))
* add ping method for health check ([0bfa0fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0bfa0fe4f70b6999a3de088858517a178d764f26))
* add property ([befa903](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/befa90353b54d8a1f8d40550bf4b4c2b3353421f))
* add property ([c8b1dfe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8b1dfe3a6e5693026eb78a0e49282dcb2697a82))
* add read notification ([b579828](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b57982878c1d48a12426ab2fa72636e01177613c))
* add read notification ([63ee571](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63ee5714232dca714fc11e08315483a7c02dfb98))
* add reschedule functionality, cancel schedule and clean up files ([46ea75c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/46ea75c84c3090fc6cae06d13ca9270baffa7e11))
* add reschedule functionality, cancel schedule and clean up files ([50ecd83](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/50ecd83b1f8d0b569141e82e5a87f36dc7aaff89))
* add reset filter button ([518a296](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/518a2969c12e41c654633ecd7c798a87668aea24))
* add restore action and get soft deleted data on DTO ([b9a3977](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b9a39774465ac55da15268d63b1e12255982ace3))
* add roles CRUD controller and fix bugs ([015d9f7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/015d9f774960359491c5ad710107ca7c49b1acff))
* add schedule cleaning change request endpoint ([0e7e967](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0e7e967911e24a992f916ffebfbdef91c57fdeda))
* add schedule cleaning change request endpoint ([a377890](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a377890d9e541b0b86c60b3654f1e3551e46d2f2))
* add schedule cleaning deviation and refactor start/stop job behavior ([08ecf3a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/08ecf3ace738721c2467d3cf33818553f6d053f8))
* add schedule cleaning deviation modal ([8f38b1f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8f38b1ffe3e0f4e5808071693c9fe63272903706))
* add schedule cleaning json endpoint and fix some bugs ([a0e4df2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a0e4df2cca9c251645b87892c46165a8e05c3949))
* add schedule page and multiple autocomplete component ([9104eb9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9104eb9be6c795d8070f2ba064b2cd2e658d8603))
* add schedule page and multiple autocomplete component ([3f94d62](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3f94d62ef4f5d8f56595bccd866f9a4dcfac6009))
* add server side filtering in datatable ([c343740](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c3437406e95d581608e0625c7f4dbb5ee431452b))
* add server side filtering in datatable ([9f1bb05](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9f1bb05e011f97b083c95d1aff48a04b4968f597))
* add service container for Azure Blob Storage ([#136](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/136)) ([bcbeb8f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bcbeb8f05e6b4077233c0fbfccfb43527fb038db))
* add service container for Azure Blob Storage ([#136](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/136)) ([8d9cf26](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d9cf260ee3804ec93c73940c29118dde4d1726d))
* add service page and components ([607d78d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/607d78d95a3a24e0cd79243fbcfb080070b60c46))
* add service page and components ([8a8acca](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8a8acca59f5fe6d5e502c7bc144d893ae655f8f9))
* add service quarter menu ([fd636b9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fd636b92035e38c7a01207e24051cba71bc2c0bc))
* add show and hide all teams filter button ([efcb4f0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/efcb4f0e1fb6de0ed874a731f6793a6d6e6cdc8e))
* add some DTO ([6f59a48](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6f59a48295c0a46cf24df54e93c46570424bdca8))
* add some DTO ([8589781](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/85897818900d47769f0174dfd6aae30f0857e46b))
* add start and stop job ([0df6568](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0df6568bf0522f854ca184babf3c2e1dd4fd1a14))
* add start and stop job ([3ab5b56](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ab5b5688b4ba613101bd92848a341f86e44827a))
* add sticky behavior to action column ([df81616](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/df816160dee1bf20165fecdf76cce6630498c545))
* add submit handler to wizard and preserve state ([16ba65f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/16ba65ffe321b82d224de78c640a4bb92af3ffdd))
* add submit handler to wizard and preserve state ([3fe6a31](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3fe6a3139da2cdfb42af6ac33b32358e9f4331bd))
* add subscription and employee ([a1a5106](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a1a510634b95305bc85c9b66989ea8a84e85c925))
* add subscription and employee ([29ef1c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/29ef1c98c261c58dc15d79d537413a57c4985766))
* add test subscriber seeder ([32730d2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/32730d2131266a232bf12531b8765458b8a17a37))
* add update profile feature ([02dc995](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/02dc995d3b8c438befcaa700cab9b1c68b9f1f4d))
* add update profile feature ([371b7f7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/371b7f7eaf291b2e8b57c1e28d2e63cd72694d2a))
* add user schedule cleaning services api ([4a7ba8f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4a7ba8fbb09930487e4b2f1a5f95ead43129782f))
* add user schedule cleaning services api ([0c5849e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0c5849e99bdac1b5ef8c24dcbd2f0622df9595a9))
* add verify email page and fix update profile avatar bug ([1431286](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1431286e31a3924b50974e86fa92cc7940630b7d))
* add virtualization to datatable and adjust the filter and fields response ([5a36e28](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a36e28cb6351636474d3ef59ff34388216491ac))
* add welcome email backend and edit employee roles ([10f7486](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/10f748648e2cee7d6e2a24ec024818eefdb4a0f2))
* add worked hours view table ([39e9873](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/39e987364c40eec329ef52cd6fe398998e79cdf3))
* add worked hours view table ([04f5641](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/04f5641e80adbfded7ea2c629ff5fc0b557de5b8))
* add worker to schedule ([9f69547](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9f69547106c25e3343ce94bd56b6763dfdc55468))
* add worker to schedule ([781102c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/781102c3fb2d5b2b0f8f50508ac5cc9b9ec10bc1))
* add workers and add schedule task CRUD ([fa73ec5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa73ec53590e1c641123c62738f7f09c3d34711a))
* add workers and add schedule task CRUD ([3471d79](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3471d79fd8a1bc263148828d1fa37723b39aba81))
* api employee and customer ([e334868](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e33486815d192587f9b65d4b3de3923e245d2b8d))
* api employee and customer ([59fad5a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/59fad5a8e57a49c1d42baf851b3986ab2f637ad6))
* application setting ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([c7f98be](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c7f98be56d250ee38224063550591a4fbcc03cc4))
* application setting ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([c3adb7c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c3adb7c7a6a7fa687011eb8239b3fa095ec96652))
* authentication user ([d0bbd60](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d0bbd602bb4b5f730547072c332abedf674a8197))
* authentication user ([1385fe9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1385fe9d203054c3304ca4ea8fc812ff46afe1ca))
* bulk cancel ([f06cba5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f06cba529f547406c936c7e930e94bd54e96d5be))
* check old db connection ([ef6e3e1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef6e3e1b6f1228bec9c216cbc13f7a5d71d31b99))
* cleaning task and product summary ([4fde47f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4fde47f9134fdd70320dd9ff5774132769187af9))
* command and job to fortnox ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([8c25c60](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8c25c605a82fdc916c6174f6e4e4b7226ec14704))
* command and job to fortnox ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([9762f91](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9762f91a30c882b11e6775d92b942e8a44081bc7))
* company controller ([#215](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/215)) ([49d7ecc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/49d7ecc81279bb2e075a93436a7485f440a6a359))
* company migration ([e358d01](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e358d019e2623477a27f0332195bc3b99bfebf8e))
* creat article and price ([8105ec2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8105ec233826b1c2721997b010d19362e8382427))
* creat article and price ([deeae59](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/deeae5993e47d8aa986f07aa36af0f50ca8ffe7f))
* create api and adding product task changes ([#97](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/97)) ([0110f34](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0110f34bc91cc6997e40a7bd9ce6f13e9cd2afb8))
* create api and adding product task changes ([#97](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/97)) ([cfb73e2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cfb73e23959d2c8a4706f1228aaddacf429fbb68))
* create axios web client and add get customer properties service ([1377751](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/13777518f1918ff0432993788b4f91192a90cb36))
* create axios web client and add get customer properties service ([4ba6373](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4ba63732dc74d433b47f39eed9ac10e8df2483f9))
* create client side and server side datatable ([0370ed4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0370ed42e7298e29bad9dbe795233706880ff01e))
* create client side and server side datatable ([882f6a5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/882f6a5bed0dd4470f1ea10da7c145197630cb4a))
* create command ([c6ec424](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c6ec424cc67d35d3c75a77d18da1473e55893e8a))
* create command ([f6e0286](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f6e02865534fd4c8b1d52b3bac3b3a0255659ebe))
* create controllers and query string trait ([cdfd66c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cdfd66c7e4bd87d3f41c3e38016e62ed420432f3))
* create controllers and query string trait ([f4e4fe8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f4e4fe8a8a6e3c4bd6bebf908af70bd54246c5c7))
* create discount view customer and companies ([ca0e378](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ca0e3786ddcab1bb099c3c0ba00e56666720619a))
* create enum and restructure DTO ([cc3c641](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cc3c641e0bf9eb54892527787c2e3df7ebd91401))
* create enum and restructure DTO ([aae8efc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aae8efce6efdf623d4b0e57e5b4aa6738dc6a387))
* create event and listener for save geocode ([0a532a8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a532a8f095811c8ab1b7ae7a52c848bbc15a28a))
* create event and listener for save geocode ([35242e0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/35242e0f2d1ee99971fcd1d6bc18eba1e49649b1))
* create fixed price portal ([9399422](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/93994220475d99fd516c2b3c413acbc5000fcae6))
* create fixed prices actions interface ([d73cb87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d73cb871941578d9d152116d833400c82211680d))
* create initial schedule week view ([#209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/209)) ([06f4905](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/06f4905e5468060701782bc15d3565bb19e70a74))
* create log auth and activity overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([666af67](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/666af6725f5b636e23c32b157bc44e76803acfef))
* create log auth and activity overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([91e7750](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/91e7750b82ecdd99e1d5ccd094edc9293af16013))
* create migration service ([#202](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/202)) ([18efa4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/18efa4d9b674d0d6b681790bf556dd86e81ebd60))
* create old db service ([#202](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/202)) ([b963c74](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b963c74c37b05104838daa931596c7df4c98d872))
* create product and add on seeder ([00d41bc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/00d41bcdbd6bc799caceb7df8896f3234570642c))
* create product and add on seeder ([19b53e3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/19b53e36b2d2f1fcdd3eaaffd4de92949b472d0a))
* create product and add on seeder ([b85b144](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b85b1445d8fcd39ee1230a6f770a555014710df5))
* create product and add on seeder ([4b437ad](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4b437ade6b28622b66a498927a611333caf38127))
* create refresh token API ([#158](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/158)) ([b387fa3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b387fa3b5852078dcfce0b1cdef5e048bcfeeef1))
* create refresh token API ([#158](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/158)) ([9d031d4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d031d48cb6964a4a6cccf8a1b44e9df5b021f3e))
* create subscription from schedule and fix scroll ([#210](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/210)) ([c4231af](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c4231afea05f745b32aa26b38c9db4bd81e5d663))
* create subscription service ([#92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/92)) ([c57e734](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c57e734beb550622d607376319d0f10e8ca0b1c9))
* create subscription service ([#92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/92)) ([3400fa3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3400fa397c103203351456796c0191bcd823fbef))
* create subscription to schedule command ([7737fc9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7737fc92ae60fa8ff356e95d07e770a5052ac7b5))
* create subscription to schedule command ([6698847](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6698847f7761dd98a29dd9fbc076bb05d523d081))
* create user onboarding ([b5e57ba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b5e57bab25de0964b8dc1425a372bb03d64e3e6d))
* create welcome email ([#200](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/200)) ([cdab3d1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cdab3d1675f558d18aee4b9d679cfb3e072452c6))
* credit ([6291ebf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6291ebf0c5acb74fe7c74c25c5432dbf6254e0d7))
* credit ([00f82aa](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/00f82aab513c4d8f6dcdc5f2d476da545e22a442))
* custom task ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([2ff0fe5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2ff0fe504cedeffaed243df4a372f7ff97b293ec))
* custom task ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([45abf75](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/45abf7579b7e98479e1f401aad795c9f2f77eda7))
* customer account ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([51453f7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/51453f7638978c013e61de0016138946abca3ac2))
* customer account ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([861d350](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/861d3508820459facac951700299bb451e4bcf7e))
* customer invoice ([dc7a15b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dc7a15bff436800bff416b7eb084a4e3196f8cc8))
* dashboard layout ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([2784a4b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2784a4b6c957d766abdf27332c0786c8381528a8))
* dashboard layout ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([d539c11](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d539c1153bed5c36fc519483d165b5421dcb960f))
* deviaton API ([2901bf0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2901bf020fe053fc900eb4d4b22da4fe1a9b4b6c))
* deviaton API ([13a3ce6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/13a3ce6e619e6ccffdb8d0d2ee7f188549126e49))
* dial code ([1ab35bc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1ab35bca96a8642ebde2c92dfb7206212ec76812))
* display list in datatable column defs and fix styles ([f720e98](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f720e9803e3899983562c21b7d4d214119cd77e6))
* display list in datatable column defs and fix styles ([ff0a458](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ff0a458594c93828e5350655c23d57bc584468c5))
* employee and customer schedule ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([c6507c9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c6507c950dd0e22504c2fce1d6b21fa21dfc2eeb))
* employee and customer schedule ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([3a7f011](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3a7f01114b7eca62be7d2ac9a7256e7b13301de0))
* employee and feedback ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([253031b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/253031b5219c34673880ac16ef901bb5d468a8af))
* employee and feedback ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([2c1c90b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2c1c90b0b2d295e400df5e1469a737189fbc1cd6))
* employee wizard ([caba484](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/caba48453ef8dcd842c385c91de37a21e01a2cbc))
* employee wizard ([73ba1bc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/73ba1bc5f729c000cdc284e1510db61314e1a2d2))
* finish login page and add components: ([071654a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/071654aa9ad6bdd38a2336d42f89b5ac22fd7c2d))
* finish login page and add components: ([62dcaa8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/62dcaa87cb74acd9cda2cf294ab988b44ec52117))
* fix price and fix price row ([4772944](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/477294456d3b8ee43eb2b7ba9257cb52a77e761d))
* fixed price initial ([3199d2e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3199d2e335ef6969261223e9f8d48e265fd22a0b))
* fortnox invoice ([ff66575](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ff66575df79c24daf78ef420cbf9ba5cf6e282a2))
* fortnox invoice ([a198b8c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a198b8c49310c3017b98a6323656c65751429c59))
* fortnox renew token scheduler ([573c6f5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/573c6f59c90ec5f8c575d6f0285937ade176be65))
* fortnox renew token scheduler ([747bf11](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/747bf11a4391e58a244fc920c6c13bec2f930226))
* generate order, order row and invoice ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([a1ee095](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a1ee095ae34d7dbf9a3ae08e7b78e9cb7f3f5bd9))
* generate order, order row and invoice ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([db45c30](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/db45c30214c17fe80594fe6419188eb3ecf26468))
* health check ([a9f6918](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a9f6918e2532ffdd07adc184ea2ffbc554d44315))
* health check ([963269d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/963269d9e7276c8aa1210e3d7fd258e94b672814))
* health check and prevent race condition revoke token ([d08fa17](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d08fa17109741a8c3cbbe731d93091c2bb847d4b))
* health check and prevent race condition revoke token ([98ee98c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/98ee98cdb159055ab2f8c52217fa8c21c4291b57))
* implement client/server side datatable to each menu ([b440c55](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b440c559382c1c0f1a1a3fb50059ae0d63f8e9b5))
* implement client/server side datatable to each menu ([493bea6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/493bea6fcef4cd400eb1a76c1c12d62363e12ee7))
* implement dashboard widgets ([#201](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/201)) ([474eec3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/474eec30500968d7cf0cad7e1309455b0e2977b3))
* init global settings ([#170](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/170)) ([b1643d0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b1643d043dd95732f944df79a09e2e16525a9f32))
* init global settings ([#170](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/170)) ([674158b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/674158b5652b6b3231ddb7caeaba21e9b1661447))
* init laravel migration ([bae5b4e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bae5b4e02f9fe6fe91eb014fc5c1d79713987f5e))
* init migration ([#168](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/168)) ([a2034e4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a2034e4b2d98c6450f4ca9779d4bf2042e783d0f))
* init notification ([0240232](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/02402325501590157aff43e9153f99b96c049a58))
* init notification ([33f730a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/33f730a44f57c8dd3bb6a0e24c0b28c6cfc15770))
* init option ([686c0d2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/686c0d22b9e51d14207b320467098b92ab5cbbfa))
* init option ([8542756](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8542756b44338390adb5e2dd31d79c3331e23ca0))
* init product task ([#97](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/97)) ([97dbb02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/97dbb027c4e71feecaeeaaa02d157d43d34105cb))
* init product task ([#97](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/97)) ([65a5957](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/65a59579138b6e97156b5462b21de63cfefa821e))
* init subscription ([#92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/92)) ([9d665f9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d665f9ea1c3290960136c1efaf1a52ab270623b))
* init subscription ([#92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/92)) ([01f83dc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/01f83dcb02a3fee30e9ef111bd4a5a411a85a45e))
* init translation ([#159](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/159)) ([53968a8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/53968a8dc29a9b3a00032cb33d89ee23c55ef2fb))
* init translation ([#159](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/159)) ([3d24e28](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3d24e285dba69ced941e42d848bc7649be61b2d8))
* initial phone input component ([403cd4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/403cd4d0e37d4728b87fca858e7cb529216e2874))
* integrate notification with login and logout and cleanup the code ([#137](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/137)) ([66aaf2a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/66aaf2a0f492ac7254a84017fb603a25226b538f))
* integrate notification with login and logout and cleanup the code ([#137](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/137)) ([9211604](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/92116049dcfd60709ded458b0aec5c867b76029e))
* intial commit implement localization ([d8852d3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d8852d32b035f55decca9eb3123e91e3398cb233))
* intial commit implement localization ([099ad1e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/099ad1e75ca5c58053e896c669a2bc3ff29fe768))
* invoice controller and route ([b2106fe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b2106fe5af088d820393ca6474f77b1d86bab9d1))
* invoice controller and route ([37bed1f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/37bed1f281f88fa38a3d00da68e3604a232f1bcf))
* invoice, order, order row migration and model ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([c73d694](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c73d6945a0a754ed9f7776ff57777a79d1ec29c4))
* invoice, order, order row migration and model ([#194](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/194)) ([7680258](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7680258d5b45415b000d6e096337af71a07bb553))
* job and task ([6b050df](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6b050dfe43977ac0aec45badd9993d824a59f956))
* job and task ([712331d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/712331decc13c1c771756ac473fdc378925f57d9))
* key step in wizzard ([#199](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/199)) ([152fe61](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/152fe61734bdba9eaf1302d9576b08be8f1ce6be))
* key step in wizzard ([#199](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/199)) ([e7404d8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e7404d8664b18d58efa21ab0c85547d3182b2f07))
* make benchmark test ([#168](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/168)) ([f4eab43](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f4eab4313f5b70c5aad23a704590e70139553f52))
* migrate customer ([9ae530b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9ae530b4fdcebf2dd48541790c281ed30e79c78f))
* notification factory ([d3c3ffc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d3c3ffcd1136751c34a6ce0fc90e2512098b0b33))
* notification factory ([100183b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/100183bcb4443e16321cf3617dc05e23f45f9384))
* order fixed price ([f35aabf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f35aabf737797d74f130b7cdf748baf24db3163e))
* overview property and customer ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([cea56be](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cea56beac80fb7463707c0aba26dddb40979fb2f))
* overview property and customer ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([63036fa](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/63036faa77db29f0ccc75bcefceea35c08032a99))
* portal menu ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([ff62a24](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ff62a241a159b74bcea97197205fa9136539f279))
* portal menu ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([367f850](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/367f85036897be7de603da0cb7e7b0cdea6df9d6))
* product summation ([e5467d3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e5467d372ff3d01493b5a283c963368592ba2ed3))
* product summation ([fa1d1da](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa1d1da43b87607c17a3cf5b52b0e3eead65c1ae))
* profile route ([3ed2c3b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ed2c3bd7f64d97089acd2e45234dbf95bcb2c09))
* profile route ([01d9501](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/01d950140052d1a2eb47ce9274395378c60e38d7))
* property ([49ff589](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/49ff58948667375f35001176e8bfb29133a55a3e))
* property ([53d1aa5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/53d1aa59d7423a09a1afedba0ef92b988d935beb))
* property wizard ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([8fcbcb7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8fcbcb752f3b353058c8c98a8fabc607a96f53e4))
* property wizard ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([b028bf0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b028bf07ccd36ed41526f55cb6a00788bcce7f5f))
* query string filter ([b9b5756](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b9b5756dbfc61b382db6542f187df2f14e359cea))
* query string filter ([df9f833](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/df9f833e8018d5df0db7ac407e00a213d14a4106))
* refactor add on feature, DTO and model ([72b80ed](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/72b80ed46ab504a92019ff2a58ea33ccd0e06d0c))
* refactor add on feature, DTO and model ([8e57f90](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8e57f90aedfe9f5c1dadc190b61e99be81c0e650))
* refactor OTP ([05af413](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/05af41334692b68b36e983e767698991586da05d))
* refactor OTP ([3155d09](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3155d09f9ad108c8e7a7b571391a928d583303a2))
* refactor setting ([70497c8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/70497c873369218906a3012ddd4cc91aa2f46bdb))
* refactor setting ([5d5a7c5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5d5a7c57652cd55221a567cabca845b5cac93854))
* refactor some factories ([27a6ac9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/27a6ac94473b9c6b0aafe3bfd172842acc7a6fa7))
* refactor some factories ([0402a9d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0402a9d7d3359636268f5b13dd355e57b3fd73a5))
* refactor team and product ([0050743](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/005074306796a9a6905bea5fa7d3acd6fafd6372))
* refactor team and product ([a2ddee6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a2ddee6cc78b8844cedc0bd5ca19ad9b3ead4b3f))
* refactor user ([d209c94](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d209c9445b2f205d5f48b888979d31e172316cd9))
* refactor user ([d72373d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d72373d22b6a5563cdf981bb2392c5b64797afda))
* Refactor user API ([0f6388d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0f6388df8398c683d4953b64b8036aa4e48dd58d))
* Refactor user API ([a5bdce3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a5bdce3a064dc23323c136bbb617eebedb929c9a))
* refactor user controller ([cdf8121](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cdf81213ed96c07e146e7404ece953d749ec1bb4))
* refactor user controller ([d0ec462](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d0ec462c27c7de30056ed7fb62b396872cb00d48))
* reschedule, enable, disable worker ([d486d61](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d486d610bd94155086723c9fbf06267715cd3471))
* reschedule, enable, disable worker ([cea2236](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cea22365374da35b3e4c384717f708512218cf76))
* role and permissions CRUD on portal ([#198](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/198)) ([2ad1d7e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2ad1d7ee07b64837a67f100929f17e4d9774a9e2))
* schedule deviation handle ([eefecdd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eefecddc40b209522d20e6800f152ad93cd72464))
* schedule frequencies api ([23d29b1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/23d29b186080116ecb8170f126bb65f3921265d0))
* schedule frequencies api ([85d6eff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/85d6eff43d7ad2e887b6c739c7b7d28946a58322))
* seeder company and order fixed price ([ba15c6b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ba15c6b5d55b5851f7b3dd369b7379f845844ccd))
* send more notifications and add reminder ([830251a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/830251af14d2db5a6b24a9d0ede59d4d8173af02))
* send more notifications and add reminder ([03f7f08](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/03f7f08caafcf5821367571fd3d2ab3b5ba32477))
* send notification on order cancellation and fix localization ([aea6668](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aea6668e30e7607718847b13eb648cdb85c23a1c))
* send notification on order cancellation and fix localization ([c781911](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c78191118292dad5e364662c7bfadc23ad0df6cb))
* send verification email after registered ([d5cb1b5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d5cb1b5c058f2820f0eed96cdb14d9cd55939a2d))
* sent working hours job ([2e80fa9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2e80fa9b1b5a115940afd6cf6aea3bb7027fee7a))
* service and add on route controller ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([a9c7681](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a9c7681d4469b03f9659305f497fa94f0db9b202))
* service and add on route controller ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([dd03e88](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dd03e882fb0b9ebb713f5396fcbd425fe35f1f13))
* service summation ([55cbd9d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/55cbd9dfbabd074eef288ab1248a140e34f4e50e))
* service summation ([695f64d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/695f64db2b7e3086f6dbd107ca9e45bafb47a00f))
* set up clock in and clock out ([3f6565c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3f6565c2ac037bb9add5f2a0c110976ee6c5711a))
* set up clock in and clock out ([bd71c8f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bd71c8f7de5e382c786c722ce24b938769f4ced5))
* set up controllers ([2448362](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2448362f98f91d21f0b855e70682eea0e9dd071b))
* set up controllers ([849f6ef](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/849f6efc426303157bc850da2c50645e68f81faa))
* set up DTO ([ccfd158](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ccfd158eee1118549b4a84219e95d330ae8111d8))
* set up DTO ([7f71ca5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f71ca52198a00ff88a5d902118725c1a4ce0416))
* set up for test ([8f61330](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8f613306c90d3adea5d9a3532c2e268ae445ad71))
* set up for test ([2192bc6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2192bc65880cca8508b0196744fd61c894b37cc3))
* set up push notification ([4f917b4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4f917b4669032db79b3f26e7e039c9e4fb3bbed1))
* set up push notification ([c400bac](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c400baceb354576f221a6e5ef9ee654cb9bf8e02))
* set up user setting ([06dd5e7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/06dd5e7ee9bf11c32a9f615d8b9f320015faa769))
* set up user setting ([28a1c73](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/28a1c73efc8547ab65841b568269c7564e012a58))
* setup api fortnox ([5c473d0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5c473d04c7d69503857d6646d47122956f2d470b))
* setup api fortnox ([f37aa2c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f37aa2c98e169eafb623798d73de46210a284f32))
* setup base for the backend ([#153](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/153)) ([09792e6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/09792e6e2051fc98b35f648b1d40b32fa422ac54))
* setup base for the backend ([#153](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/153)) ([b15fb9b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b15fb9b6babfdb43be7dd3e0f2ecef42d9989017))
* setup permission based authorization ([6c74034](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6c740340c719164315b27e17bb2b66c0bc7fc5ea))
* setup permission on frontend ([a502c3a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a502c3a2716af69a79930d34ef11401f02a7abe2))
* setup product and add on ([da33977](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da3397787e1468c2dc9d8ca47abac018e664dfeb))
* setup product and add on ([4e71416](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4e714165d4c7f2109e1efd4b96fcea3b22220ff1))
* show the area on schedule content ([caadcf8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/caadcf806cd60e10287ef820c5f8c1b0bd0cd3bc))
* subscription and add on overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([3314d6d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3314d6d8fb62fa06ae82477fedad88ef0eb87090))
* subscription and add on overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([57e28c1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/57e28c1f03f252e68e60ff22d44e2e2005739ede))
* subscription and service task ([#190](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/190)) ([de4abad](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de4abadf51aebb2e5448b719d373829380af3460))
* subscription and service task ([#190](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/190)) ([5e606c4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5e606c401a1e5179799ceef2aa566a9a2f8e6160))
* subscription wizard ([fd57ef7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fd57ef799519c80a77610ef07b0b28e35b007b04))
* subscription wizard ([45d65ed](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/45d65edcc2095d5971d0d6552e6fd47afbe4c213))
* subscription, property, address ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([3d19147](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3d19147bd3718d514414c141a6ab139e3cd8ce27))
* subscription, property, address ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([e3b3db2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e3b3db228afecb07b14ecf907bad32f59b82f2d1))
* task overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([39f8962](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/39f89622219521e76c9a1c8eb4af1d0cef7652d5))
* task overview ([#163](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/163)) ([cd59875](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cd5987558ba3604fcfb0d1da20fea031073c012f))
* translation ([2b09e70](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2b09e707125cb25ccbe69eb236cfb892561c7392))
* translation ([24bc4a4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/24bc4a49799ad648e564fc019039b35038d2172b))
* translation service ([dceb2c4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dceb2c43b5fe907ce5160588520e48517cb3a10f))
* translation service ([4b35702](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4b357022a8440734907802045e5eb9a14c91923a))
* translations ([4a25f8c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4a25f8cfab6a2890dddea68d024d74a6ac8ccdd8))
* translations ([bc8ec4b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/bc8ec4bd40fb33bf221544ef6b0672a78fe5f3d5))
* update cellphone api ([eb44660](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/eb446600351f5f37ae51bd2ee7597678c6aa0bd9))
* update cellphone api ([96ce08e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/96ce08e958f7fccdb06e91902543d27cc42c6953))
* update docker, add new endpoint, and fix bugs ([198a069](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/198a0697ffb26fed94f0bd3b3beabbb60ecd99f4))
* update docker, add new endpoint, and fix bugs ([2cf44c2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2cf44c20f5f9245834f3ceeeb43770dd4ef60391))
* update schedule cleaning ([0a9af1b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a9af1b3a0774f902c11f3f2566ae9056d403941))
* update schedule cleaning ([8c0f20d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8c0f20d9d72c1530701e3347fb74bf6fc1a2b071))
* use custom template for forgot password ([9b06f5b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9b06f5bdd3fd6d39ee102c8bdacc9ee4b8db6055))
* use custom template for forgot password ([1480940](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1480940dd5b754cf502b3c5763e2138b0f2bc89f))
* validation role in login ([2a74328](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2a7432826114a20c39e11c5318b0a163e5ff70fb))
* validation role in login ([84bc685](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/84bc68512b3a72f74b0a22b6c58faccc14082597))
* verify cellphone on first OTP login and forbid customer login if no subscription ([6fe65c8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6fe65c899309684dc8842551cdb982a350379f3a))
* worker ([1ff9ae3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1ff9ae3a318a5e41a5de730e0599e70c1d0f4b55))
* worker ([418e35c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/418e35c2cb45952fba05712efca29bd0db087ac0))


### Bug Fixes

* add checkout test ([0cda0fb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0cda0fb545aef56e74bc426f7a103fb0182304ce))
* add checkout test ([ab12dea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ab12dea632e212db60e8cf5cbe60b3bb6405dc63))
* add prefix to month key to prevent JSON sorting in JS ([0aa8bc4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0aa8bc4133eb5a848f83b42705a9d60e5de056ba))
* add prefix to month key to prevent JSON sorting in JS ([79c603c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/79c603c8f4aa3f7c9ae0cf845f6969d844df3e1c))
* add range for schedule should start and late ([0c8754e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0c8754e5909ce8e43086db333f3667dadfcef834))
* add range for schedule should start and late ([b09dddf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b09dddf79c1c15c4dd55d1546c9e3cf4b15f1869))
* add worker DTO and actions not hidden on readonly schedule ([b7b659a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b7b659a6b4da32d28b489eadbaa76ad9d1014f1b))
* add worker DTO and actions not hidden on readonly schedule ([4cdf062](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4cdf062e2cb85b1321ec477c136c26698130978f))
* addon tasks not showing on schedule modal and disable notification in local ([d90098f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d90098f0bdc425a8463c964c69293532dad41f67))
* address cache not removed ([6800633](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/680063312bba92af4b1a3b89aa41b24db942c472))
* address test case ([5c7bbc4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5c7bbc4899594845ceed576ecd5a9f05e5f6e0c3))
* Azure Blob Storage authorization and storage path ([15a25b6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/15a25b66e7c27bf40f6ece023106fa166c2c65b6))
* Azure Blob Storage authorization and storage path ([f88552a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f88552afd8a05dd8f9e9cde02958d2ca388009ff))
* body margin bottom ([#210](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/210)) ([2811181](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/28111814cb316a2823f651a9bae7d00a7ae1644a))
* broken images ([4258421](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4258421f951558edea8fa4c60a5aad0b9a4c3e76))
* broken images ([f761c4b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f761c4bd337eac1bbba846244c95323a4d9e055a))
* bug and missing translations ([7fec46c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7fec46c9b660a9ce46dfcf058f919efb474d8543))
* bug and missing translations ([68f1007](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/68f1007c65dbe203f3f4d8450ed7ba427b0af7eb))
* bug on start job and wrong sent invoice behavior and attendance transaction DTO ([92469c0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/92469c0940f3aae2c12772c4dc0b0fdfecbeea8c))
* bugfix availability ([55e8e92](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/55e8e92937ce107f600fa293a45b9ffc26a5ce9d))
* bugs ([4324d68](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4324d68212042a588df4bdaa8c0868f87da6532c))
* bugs on service and addons ([d297087](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d297087caf6c324ba018b49c17adad8ea9add09d))
* cache only success response ([7f795c2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f795c21cafc305129205f8faec3b4b7cde18a6e))
* cache only success response ([37bef68](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/37bef684a24ac25655c6c67bcc3e54aede20a2a4))
* camel case to response ([4f0428b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4f0428bf95749f68a940a5fba13dabe2eedc3423))
* camel case to response ([8d5f016](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d5f016648b9aae494645ae6bd924af943f6a792))
* cancel schedule ([8d2ddb3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8d2ddb361c47749a499cbc3203c8f9fdf8baaa64))
* cancel schedule ([cf0d3d8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cf0d3d8796e5e81a770b03f6b0aba7b0f90d4019))
* category_id not fillable and deleted service not fetched ([0f5d1f6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0f5d1f66e1c776a957b855c8e2ac2f7fbdaf177a))
* change request widget font size ([#203](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/203)) ([f682eda](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f682eda53b5d23ae5887a2a1d84088333ffeae1f))
* checkout endpoint and clean up some codes ([522da15](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/522da1589627ed141b79c39c6cb4ec24991ae897))
* checkout endpoint and clean up some codes ([e28b136](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e28b1360b2f060c3f3be77b1d7f6ab2913b39dc8))
* command scheduler ([b35fb6b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b35fb6bdac046bdc9c4e31f8592df28cf25ff740))
* command scheduler ([f72aae0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f72aae0d550809774cc08f06f771bb0b8274ce35))
* company wizard and property keyPlace not filterable ([6783aea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6783aeac08cf2bce5dd909b171f96191706b7a26))
* condition and missing initial value ([0962209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0962209cdf1963b2d40940d8d6f5e417c3a5cf4f))
* condition and missing initial value ([c8cddaf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8cddaff36e364b0e3f830f4dc971a545b0d2033))
* controller type and doc ([5a99191](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a99191bcc2c62d6e0b5b92ae31764d3fe622798))
* controller type and doc ([de9b1df](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/de9b1dfa86226fae167b43ade6076e45ff1205e7))
* create and update request dto ([90dcfea](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/90dcfea3e3abeed734d273afab648155aa59cecb))
* create and update request dto ([11877d2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/11877d2e7d69f2db945e3c92849c7e9df5393e6a))
* css not loaded ([7a963a8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7a963a8bc0bf4bb6cd68122c0844b363983465be))
* cursor paginator wrong total and exclude empty sets on exact filter ([0819db7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0819db7f232cb8fe7c3105a2d94bb9e97333aa62))
* dashboard route ([fa1c933](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa1c933207e1b12b2bb248ffd8e52d6e70d73c02))
* dashboard route ([b02176f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b02176f044ca6c7f8a6f45974b496d3fc5e88169))
* datatable server side filter ([7772972](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7772972640f747f314fdcaa1098222fa6c28bf52))
* datatable server side filter ([2484cac](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2484cac6c986abacccb6eb1fa1ed066bec1f7fc9))
* day view and set monday as the first day of week ([46dfa3b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/46dfa3b63f4668e2dea9c695f9db36ecb637eac6))
* description not included in response ([b4da717](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b4da7179ff4d692adf0c1ad847cdf5c2ec53e72f))
* docker containers entrypoint ([25e77e6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/25e77e66eef036b4766b21ee4b75928d0587d055))
* docker containers entrypoint ([2adf8ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2adf8ff9ab8955087390fc959e54a9a2d9053a09))
* double scroll on datatable, etag error when throw exception, and deviation route overlapping ([3ca90af](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ca90af6ea3d8b90dc866119164dc481b60936bc))
* email validation ([#205](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/205)) ([6368b66](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6368b66de29849423f776272437b001833105031))
* emails subject not translated ([457c89c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/457c89c1bd3f55e140b8b19b424fdcf4347b37bc))
* endless late end job reminder ([ae17cee](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ae17cee20daaba392da02db8c6abefe6a6fdc0f4))
* errong lang ([33908e6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/33908e631ff4ad9fd73c7c4f736c0c273b59f87b))
* errong lang ([497628d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/497628dc681d59c6a837535938270fe614e35c60))
* error closure on queue job and scheduler not running ([03f3824](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/03f3824d52c4ffdb9952c663a541b27aa1be3f20))
* error closure on queue job and scheduler not running ([fae404b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fae404b50b68b5111b76c069cb4c50760a61dff7))
* error page not displayed correctly on dark mode ([a6b2cfe](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a6b2cfe587c92275fa7b7db84e21a4dc4c559494))
* file name ([7f03aa1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7f03aa1b7d7b95c665bc81684db884d7cd55b2e5))
* file name ([c7f1564](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c7f1564bde75474c47328128d1e4157920b5f8bc))
* filter month and casce delete user ([38bd083](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/38bd083201edc7fa04c0d53bddc233027714342e))
* filter on polymorphic field ([7a9175b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7a9175b6f6cd37b246677341638b88459d05f5a3))
* filter on polymorphic field ([9ea10d5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9ea10d5e18fb22aa975f3088406bf099c7e23485))
* filter transform value ([42e1b2f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/42e1b2fb400e1408a60cce915c2a61282e7dcdb8))
* filter transform value ([879406e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/879406e396390b1c411d863efd0280c094d1ec9f))
* fix multiple bugs and errors ([#205](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/205)) ([395d19f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/395d19f89db45369bca1c7a5d4e41f099fdf2269))
* incorrect behavior of can reschedule attribute ([be39d1c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/be39d1c32c9f4af1f044cd5362e755e423ae4b5d))
* infinite rerender on server side datatable ([b3b668e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b3b668ee5ebedf6962de2ccfc5a612225ee0019b))
* infinite rerender on server side datatable ([ada3d16](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ada3d1658058ad87a8aff6c747017630adf44a74))
* infinite rerender on subscription wizard ([9c0a6cd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9c0a6cd1cbf44bfa685650193e22e1dc03e4d5cb))
* infinite rerender on subscription wizard ([9651020](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9651020e769ec13e8ad5679e04a6c6da3f71748a))
* input and autocomplete view bug ([5a754b7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a754b7cc6f82ae3402f84a507934ed0fe1fdcc3))
* input and autocomplete view bug ([3c0683e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3c0683e54874b1af57c0a96a1a9bd557dffc445d))
* input variant style and required validation on multiple autocomplete ([d1fad14](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d1fad147a7527056dc3beb4663ec930f1489d69a))
* input variant style and required validation on multiple autocomplete ([1d024a4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1d024a4b410d85f83929daf3dd4f432227dd1956))
* keyplace overview table interface ([68ae677](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/68ae67778039a66bc9fc69757c0805d95ccf8ecd))
* laggy when too much data on autocomplete option ([4674ee6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4674ee679e3b019b47c5dfab2f397e7865f5d9f4))
* leftover cache tags ([d2b781d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d2b781db0d4f530f50b52fda54e1cd07c1b1645a))
* map coordinate not changed and area input wrong behavior ([5a0b1fa](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5a0b1fae671efa4b669a92fe6d7e0fd02249babd))
* map not updated when country/city changed ([b6b6989](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b6b69890bb29237650e267328c9e2092ea3f0c53))
* Merge branch 'feature/199' into feature/168 ([f93e31b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f93e31b704fdc0b9a95c16fd833bf3fad9706c2b))
* Merge remote-tracking branch 'origin/feature/163' into feature/168 ([ab6e461](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ab6e4619ae55a3a7c1d938f5273c8460093e8b30))
* Merge remote-tracking branch 'origin/staging' into feature/168 ([74353e9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/74353e9fac93af106c1902a48d3d713c18e620e7))
* Merge remote-tracking branch 'origin/staging' into feature/168 ([6e1431d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6e1431dbafc82d99cf7b1bebb5e0a8945b0bb0e6))
* Merge remote-tracking branch 'origin/staging' into feature/168 ([72b4d0c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/72b4d0ce4e27a085108b912e9f9993fc8d0563fe))
* Merge remote-tracking branch 'origin/staging' into feature/168 ([6db0c1e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6db0c1e8a5640fd483bea553d380a8f2b43effa1))
* Merge remote-tracking branch 'origin/staging' into feature/190 ([14918c2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/14918c2c48f83216cc20fc4997c6e7a5480bee98))
* Merge remote-tracking branch 'origin/staging' into feature/190 ([c8f2dfd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8f2dfdbc60711669f3c1459d27500ee458644cc))
* Merge remote-tracking branch 'origin/staging' into feature/194 ([f9c74e9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f9c74e927a9fe151d987af87cf821f0a2a1b6a05))
* Merge remote-tracking branch 'origin/staging' into feature/194 ([7186f40](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7186f40545412e3208156e40d0ca75e0f627274a))
* Merge remote-tracking branch 'origin/staging' into feature/202 ([ef69e64](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef69e640d0db08725a004e9fd4bcdfa7a0eba5fc))
* Merge remote-tracking branch 'origin/staging' into feature/202 ([451f030](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/451f030dd26345db5af116a4b3d696a5d7d8cb72))
* Merge remote-tracking branch 'origin/staging' into feature/203 ([4f0e6ee](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4f0e6ee4743c6d70245073d10d89a02ba2341bcf))
* migration db ([4b35815](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4b358159e8935185dea6ef2e956f069d02a41e3f))
* migration db ([0a37792](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a37792764149098a948d04244968a60a57b3126))
* missing casting on square meter ([d183109](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d183109bb49539e49b75cd6c6d36d740f52eb573))
* missing dependency on dialCodes, missing value on phone input, and use default value for disabled input ([64a84a5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/64a84a5348af84aacdd30db256199fd6577fccf2))
* missing doctrine/dbal dependency ([d37cc70](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d37cc701cce357393b906cf3cdcb7d52b73a0b21))
* missing doctrine/dbal dependency ([475f02c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/475f02cb9eab0f36576168ee9a25ae365b01f6d9))
* missing DTO ([d77a640](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d77a640f5c576b4f80231643d1d6086dab424a04))
* missing import ScheduleCleaningPolicy and uncomment test command ([b418deb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b418deb9b708812f8584a5ead37a177b10797f12))
* missing import ScheduleCleaningPolicy and uncomment test command ([c18d402](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c18d4022adec99107ff48834a81d73e6d2204a26))
* missing in dto ([f02476f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f02476fbb40ce174d752356db4117b4bc8395692))
* missing in dto ([c9c8e48](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c9c8e48eeabf9406ef0813a4ae37ea03af4a2366))
* missing password confirmation field ([b1ec589](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b1ec58927ff8dd0db38db57dcb5bb1617a48c2c7))
* missing password confirmation field ([27f95aa](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/27f95aa3326242b3c83f68990240ae9d3162010e))
* missing permissions field ([9d25f87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d25f87e33a27c766eac5a37a8f0c55efb780fe0))
* missing translation and company showing in employee ([cede39c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cede39c0d506e89fad0ded26d505b75f562e519a))
* missing trusted proxies ([e10c085](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e10c08518988bcfc6289f8bb8fd17a4856876a8f))
* missing trusted proxies ([5beae57](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5beae5777586317fe8a2ac801a1e5cd3aa914ac5))
* missing x-forwaded-for and x-real-ip header ([049f657](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/049f6572657fae80232a6cc70e8fec980b340edc))
* missing x-forwaded-for and x-real-ip header ([8315993](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/83159935cfdefc854b07e0a0cc4c9f891665b2ca))
* modal size, locale string, and datatable virtualization on modal not rendered correctly ([1a497c1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1a497c112f4ad7b5af877916566ebbe79e381d03))
* move notification type to payload body ([befe45d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/befe45db03f29ca3e47d05bcfaa50c90513bda56))
* move notification type to payload body ([7fd448b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7fd448bcc6e25d3fd3d25b2c42b2d6f45fb215a9))
* no default value in primary panel ([c76a3ba](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c76a3ba58de228b7bd78f64ebf15be0d5b4e30d3))
* no validation for multiple same product added ([27668a0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/27668a006cfbcefaf2e7f16f9c5c2127444f2d1d))
* no validation for multiple same product added ([da3bd7b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da3bd7bd96d384bc22ce9d5ea1fc0010cab19da7))
* notification body not adjusted to user timezone ([cff97ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cff97ff60e892fbc8c65a27e22d9c53a517781ab))
* notification body not adjusted to user timezone ([d6029fd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d6029fd7ea681397b99c6f356aedc4307de800a1))
* notification format not compatible with expo-notifications ([8ce6d81](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8ce6d81fcb48ff9078ada54beb865ff046a91c5b))
* notification format not compatible with expo-notifications ([c663043](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c6630433d85dcb83b236ba12e0700d5e68d302b2))
* notification payload ([cf3a928](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cf3a928400262313d3e2be6cc84bedcf62cb0e26))
* notification payload ([ee6d893](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee6d893ac644aefd298983ec5502d79bc29831c5))
* notification payload and user schedule cleaning filter ([49ca7f7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/49ca7f73de47a8f0d274f6748dc4b69813ce3c49))
* notification payload and user schedule cleaning filter ([9207a38](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9207a3873e519649b972170a8a1dde700e5a42bf))
* notification trait for team ([a6592d7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a6592d780020c7741181624720751356cfebfe01))
* notification trait for team ([9dc2bd6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9dc2bd6c9c7b15db5bb1848615580d5495762075))
* notification when user has customer and worker role ([f86ce43](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f86ce43a96d61abdee68871008ff8b686418df2e))
* notification when user has customer and worker role ([3d496eb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3d496eb21ece2c272deddb6d9dc50555864a9bd5))
* part of schedule view not displayed correctly ([76311f5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/76311f520d74300967eed3738443cc0dc1aac741))
* payload rule send notification ([0a712c7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a712c7e63b7cf2c17d97fc923170ec813ae48d6))
* payload rule send notification ([1ec3a87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1ec3a8788e18ea35b41ba99271d19f0c52afd5f9))
* performance and bug ([#209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/209)) ([5efde8c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5efde8cb16f7af4ccd7940294b1c0a2bb16c3a46))
* property and invoice shown is not according to membership type ([2f0a4c6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2f0a4c6b25d5dd81cf77e200150e43430f82a74f))
* query schedule cleaning and cancel schedule ([f46290a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f46290aec434f5f1e620d8ef39ef757ecca14016))
* query schedule cleaning and cancel schedule ([c341a7f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c341a7fe06ed758f5a1614d8647fd3cc750966de))
* query string ([6ce9556](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6ce95568d47f72b7ec31995ef5bb48c3dab03eff))
* query string ([f47aa53](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f47aa5383904d1dabd63df5b7d1f4635fc76cb67))
* queue worker not running ([85856e8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/85856e8a46a33a942095796ae4f22f87a0b2b264))
* queue worker not running ([772b03c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/772b03c1056bb547325c98da7c03d09e4437095d))
* readonly bg color on dark mode and renderDefaultComponent condition ([7fdd7c4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7fdd7c4ad04aaebed0adeda7a5a62276e131c938))
* refresh token race condition by adding throttle ([56e0f27](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/56e0f2778bc155406f0eaa120508d4f53c86276c))
* refresh token race condition by adding throttle ([cfb076d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cfb076d6d15dc374d883f400259b516c4b7c1fce))
* reminder for booked schedule cleaning ([53b1560](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/53b1560846f1b0002afbb6b4381811a616a3eacb))
* reminder for booked schedule cleaning ([3ea3605](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ea360565b916f05f853448f86eb205acb040b9c))
* remove dd ([fcdf3e8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fcdf3e89779a7e1580587adb849ae48ce1e2dc9b))
* remove dd ([3a7e46e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3a7e46e901e3ec6712f141e52ae429775d071add))
* rename class name ([fa50a83](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fa50a8398b2b6e118c885e87f1b03dfe27535d38))
* rename variable, prevent confusing ([986c1e6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/986c1e6746de8c3c1cc5c41875c5cc91e0cbc187))
* rename variable, prevent confusing ([af9d9dc](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/af9d9dccadaeaf4f4e7ad5b4464bfe6561300ac7))
* reschedule and placeholder not change color on moved to different team ([0043afb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0043afbf60dd2ec7c30887faa34d1a4b8ea251d1))
* reschedule and placeholder not change color on moved to different team ([0a26e02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0a26e02f2992cb3a2bd28b79f6799c3e80b8171b))
* reset password functionality ([3c05d10](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3c05d10672412631adf3418aeab4fd0f1e2922c6))
* reset password functionality ([ccad513](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ccad51337b48fe92bf9731e7d6f4b3cf3fdb92b7))
* resize observer not observing the table when page load is too long ([e0b7569](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e0b756963a23d59f5932861e0f2c8aa094d1d02b))
* response code ([ee990d6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ee990d6630f654255b60d737da7a3f8062a64277))
* response code ([d1cc55f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d1cc55f08468ac7f01bc9047d04c9d42b53c3596))
* return string in generate otp ([4dd70e8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/4dd70e896451c6c7a0ec68554beb63c0882d7baf))
* return string in generate otp ([b3ca03c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b3ca03cdca014ee1d6ccdb5f2076800ccf42cd61))
* round size in WidgetSummation ([#209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/209)) ([b8be181](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b8be181aa6c78d1341910066b8441f99a4ab9e6a))
* schedule bar size, search available ([#209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/209)) ([34f279d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/34f279d8ea3edf45e5c391bedd658326fe4999fc))
* schedule employee, property, and seeder ([796bb01](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/796bb01f547144f4e9405c235828da55db1dc9d9))
* schedule employee, property, and seeder ([03fad07](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/03fad0724b2c486ec9662e075a74ed4231cc29c3))
* schedule selectedDate navigation ([#209](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/209)) ([7017ca7](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7017ca7478a50715da7ad31ec531244285616861))
* server errors not showing and behavior bugs ([381ce6d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/381ce6da3a351a124b0eed678d53ef8d374defe5))
* server errors not showing and behavior bugs ([e181088](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e181088ffeb206ff50a71056ab8219359cf2f4a8))
* service test ([ef162e0](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ef162e0b4eb261b59728bc7e6b320927f632c8d0))
* set hub when X-Requested-By invalid ([da92206](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da92206af7011f17c5b49f74c456e05884b41dae))
* set hub when X-Requested-By invalid ([3ec6ee6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3ec6ee67d1cf69f34dce57c92140b825bca8bab9))
* shared rate limit between endpoint or same user ([e81bbf3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e81bbf3dc1dd7584b49869fe206a51a974ac9116))
* shared rate limit between endpoint or same user ([a4494b2](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a4494b2840b965f1dcb99dc89eca8fb7750f68cf))
* some global setting and option ([cd0421d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cd0421d892c69ad5429ee902fb3c2cae5b87dded))
* some global setting and option ([2ebfaf6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2ebfaf619562c0b9f2596387b404585ebc035f14))
* some migration and flush schedules in scheduler ([f709794](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f709794c1fed6359aa320edc964e84065cd9a15a))
* some migration and flush schedules in scheduler ([cc00e0e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/cc00e0eb25fbb364b6d5b180ff4ad74baf64489b))
* start schedule reminder translation ([dbd5575](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dbd55759544665ca05eff4ebdab2a5525e88ad38))
* start schedule reminder translation ([b182f3d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b182f3d29419c4f81ed1fc5d4779a5d121726b4c))
* status code and unnecessary code ([43903dd](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/43903ddc94398a8536e13cf93f385ab1d0bb2af2))
* status code and unnecessary code ([91d7f4f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/91d7f4f491c5d270517445661f694a11cd8119af))
* subscription availability validation logic and subscription table view ([0cae0a6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/0cae0a6cf38a2468bc61fad3e269333ca57ebd87))
* subscription detail not found and wrong wizard store ([a2fcbcf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a2fcbcf2daf3c6819de44e6ec4000468d5239cd5))
* subscription once ([acc5e00](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/acc5e00e16f994ba48e5488f3b9171f71e368182))
* test ([8af4ed9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8af4ed9a69be6b7ebe94382a004c5fd229c6a222))
* test ([aa38abf](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aa38abf6ee02012c003b664be02e03eb2af7f009))
* test ([1d160d6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1d160d64949754965988670e954e2deb2eb401a2))
* test ([9d3f762](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9d3f762c42298c9aff23222114c6d372054bbd0e))
* test case ([dffeeb6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/dffeeb63b84500f6046a949cafc45d64b893005e))
* transform value on meta field ([a27f9b4](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a27f9b4c083072795e7c8248c172527b3fccefa8))
* transform value on meta field ([2f05b4e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2f05b4ef6e0a59c8508fadf7a40ff29c5bde1a9c))
* translation, required, and valueAsNumber ([#205](https://dev.azure.com/downstairs-service/application/_git/laravel-app/issues/205)) ([7966c87](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/7966c87bdffeeb686aaf42cd469b0b6a256f6255))
* typo ([a43ee4a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a43ee4a4eea6dce110a118b52abe2936aa05cbd4))
* typo ([67324ed](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/67324edfee6e3651e57e16c25a10aa1d87be85f7))
* typo ([6890942](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6890942fc277e750e0666cb68c90b9216e977687))
* typo ([571e32a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/571e32a61c4a297de0d7630e8b533f028cdb64e9))
* typo ([8265aa9](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8265aa9f70311eaca75d4bc0cb4f06917f6134dc))
* typo on seeder ([c9fcb4d](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c9fcb4d08ef8587a347dc336435ab3a315891716))
* typo on seeder ([5dc6a29](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5dc6a29015d48c904adf22f6b2c21ebcd90909bd))
* typo on the filter ([d8b3e64](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d8b3e64ff2512442cc7b99efcd391a02b0ec475c))
* typo on the filter ([ec039fb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ec039fba73a594bed601601b3f8dcacd28d5a7ed))
* typo query filter ([00eb64e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/00eb64e67788494fdb84cdb5d3035abad428fed1))
* typo query filter ([ecb28cb](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ecb28cb35687790c7ddd1b14a16ab245ba485682))
* typo, add validation and remove unnecessary code ([fcb43d5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/fcb43d5987acca1ff8a015868c103cd44b7ce45b))
* UI/logic bugs on portal ([6047f5c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/6047f5c46aac083829f1b5de9c8f048ad02435c8))
* unable to create fixed price for company ([ca367a8](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ca367a823de2a0c0da3629ac0ac4f75c974a58f8))
* unable to read vite manifest.json ([15dda6c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/15dda6c9362887452a299e9d8ec38bfb4ca370ce))
* unable to read vite manifest.json ([ecb1205](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ecb120532b02a51df8bec0ba1cc313600052611e))
* unable to restore team ([aa85b5b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/aa85b5bef7fdf963b8adbd3748b299a6dd8df2e2))
* unnecessary code ([e5698d5](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/e5698d5cc53a4384af2975c30909f148778304cb))
* unnecessary code ([73cf77a](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/73cf77a87e870d36aa0c731b403d2bf0edf34ce4))
* unrecognized filter got removed ([1bb9829](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1bb9829dc71f1be36facd242b5050b81e1d5fca4))
* user address controller ([f3e4cc3](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/f3e4cc32d42b05366fb82f6522161019f37a7361))
* user address controller ([a2e0f3c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/a2e0f3cc649af05c5e4db66ba472228412c143fc))
* using wrong method to check if collection is empty ([5e21f45](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/5e21f45c05f365a1543615d165284ddb461065ef))
* vite production build ([9847926](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/9847926243ccc9a825ce6ffd94074a608ec943b9))
* vite production build ([ae46271](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/ae462719f6474956ff1c134dd5d0b8b3c2647304))
* weekday not start on monday in sv locale ([82285b6](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/82285b613ae97eda9cfdedae0091c7a3d2e3ec80))
* wizard and prettier problem in PR pipeline ([3734d6b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/3734d6ba3a1bd2a3eed9031229fb5577730f9696))
* wizard and prettier problem in PR pipeline ([2284303](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/228430394b04579672020697eb412ab78c634106))
* wrong api endpoint and redirection ([c8c9745](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/c8c97451f6730ac4b9372da61a1d517d2a8c042b))
* wrong behavior of refresh token ([68bdf02](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/68bdf023f1772893b2d10502661dbb4c6fad10aa))
* wrong behavior of refresh token ([8448c29](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8448c294d723954467e7dcd638c55fc749b8e42c))
* wrong behavior on global settings and deviations ([d6e77f1](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/d6e77f13fef433843c2725fe02f5e063f6bcac07))
* wrong endpoint to get properties and addresses ([2d5c69f](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2d5c69f9af828452b046d16280af8fd7d2e4fcf5))
* wrong key on schedule added notification ([1b71b89](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/1b71b89bcc194837eeb943890f506a23701ba98c))
* wrong option selected when using enter key ([da2ce7e](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/da2ce7ecc28b1c86f9fe8e9609210b84d6a19233))
* wrong price and quarter used ([b10088b](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/b10088b94312e345e18a195f6e15e6b9ac5e1e71))
* wrong schedule endpoint ([8833fce](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8833fce92dbf0294d1f6dba9605912a996255815))
* wrong schedule endpoint ([2fbeb1c](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/2fbeb1ca0344cff4444a43ad9570597149f11665))
* wrong start at/end at when redirect to subscription wizard ([8849562](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/8849562feb2e81f01c7ae012750cac2896b7d73f))
* wrong use of toDayjs and include cleaningTasks field ([19e54ff](https://dev.azure.com/downstairs-service/application/_git/laravel-app/commit/19e54ffdb49c6b4938ce7907f174a4a97a66c70c))
