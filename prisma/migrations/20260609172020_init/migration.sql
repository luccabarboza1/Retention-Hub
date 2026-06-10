-- CreateTable
CREATE TABLE "customers" (
    "id" SERIAL NOT NULL,
    "client_name" TEXT NOT NULL,
    "company_name" TEXT NOT NULL,
    "segment" VARCHAR(100),
    "company_size" VARCHAR(50),
    "instagram_followers_count" INTEGER NOT NULL DEFAULT 0,
    "email" TEXT,
    "related_emails" JSONB,
    "monthly_fee" DECIMAL(10,2),
    "contracted_at" DATE,
    "canceled_at" DATE,
    "tier" VARCHAR(50),
    "plan_name" VARCHAR(100),
    "created_by" VARCHAR(100),
    "updated_by" VARCHAR(100),
    "deleted_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "customers_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "products" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "external_id" TEXT NOT NULL,
    "contract_identifier" TEXT,
    "product_type" TEXT NOT NULL,
    "plan_name" VARCHAR(100),
    "attendants_count" INTEGER,
    "host_services" JSONB,
    "consumption" DECIMAL(10,2) NOT NULL DEFAULT 0,
    "status" TEXT NOT NULL DEFAULT 'ativo',
    "has_chatbot" BOOLEAN NOT NULL DEFAULT false,
    "has_ai" BOOLEAN NOT NULL DEFAULT false,
    "has_implementation" BOOLEAN NOT NULL DEFAULT false,
    "external_created_at" TIMESTAMP(3),
    "created_by" VARCHAR(100),
    "updated_by" VARCHAR(100),
    "deleted_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "products_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "product_changes" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "product_id" INTEGER NOT NULL,
    "change_type" TEXT NOT NULL,
    "delta_consumption" DECIMAL(10,2) NOT NULL,
    "created_by" VARCHAR(100),
    "updated_by" VARCHAR(100),
    "deleted_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "product_changes_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "cards" (
    "id" SERIAL NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "product_id" INTEGER,
    "status" VARCHAR(50) NOT NULL DEFAULT 'Aberto',
    "priority" TEXT NOT NULL DEFAULT 'normal',
    "started_at" TIMESTAMP(3) NOT NULL,
    "deadline_at" TIMESTAMP(3),
    "finished_at" TIMESTAMP(3),
    "ticket_origin" VARCHAR(100),
    "ombudsman_agent" VARCHAR(100),
    "ra_claim_link" VARCHAR(500),
    "rating" INTEGER,
    "first_response_hours" DECIMAL(10,2),
    "ra_public_response_hours" DECIMAL(10,2),
    "usage_time_post_ombudsman_hours" DECIMAL(10,2),
    "contact_reason" VARCHAR(255),
    "reason_details" TEXT,
    "responsible_team" VARCHAR(100),
    "applied_solution" TEXT,
    "created_by" VARCHAR(100),
    "updated_by" VARCHAR(100),
    "deleted_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "cards_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "chats" (
    "id" TEXT NOT NULL,
    "ombudsman_card_id" INTEGER NOT NULL,
    "started_at" TIMESTAMP(3),
    "closed_at" TIMESTAMP(3),
    "first_response_hours" DECIMAL(10,2),
    "created_by" VARCHAR(100),
    "updated_by" VARCHAR(100),
    "deleted_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "chats_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "chat_agent_interactions" (
    "id" SERIAL NOT NULL,
    "chat_id" TEXT NOT NULL,
    "agent" VARCHAR(100) NOT NULL,
    "interacted_on" DATE NOT NULL,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "chat_agent_interactions_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "card_comments" (
    "id" SERIAL NOT NULL,
    "card_id" INTEGER NOT NULL,
    "author" VARCHAR(100),
    "content" TEXT NOT NULL,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "card_comments_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "card_activity_logs" (
    "id" SERIAL NOT NULL,
    "card_id" INTEGER NOT NULL,
    "actor" VARCHAR(100),
    "action" VARCHAR(50) NOT NULL,
    "from_value" TEXT,
    "to_value" TEXT,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "card_activity_logs_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "related_cards" (
    "card_id" INTEGER NOT NULL,
    "related_card_id" INTEGER NOT NULL,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "related_cards_pkey" PRIMARY KEY ("card_id","related_card_id")
);

-- CreateTable
CREATE TABLE "tags" (
    "id" SERIAL NOT NULL,
    "name" TEXT NOT NULL,
    "type" TEXT NOT NULL,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "tags_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "customer_tag" (
    "customer_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL,

    CONSTRAINT "customer_tag_pkey" PRIMARY KEY ("customer_id","tag_id")
);

-- CreateTable
CREATE TABLE "card_tag" (
    "card_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL,

    CONSTRAINT "card_tag_pkey" PRIMARY KEY ("card_id","tag_id")
);

-- CreateTable
CREATE TABLE "kanban_columns" (
    "id" SERIAL NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "order" INTEGER NOT NULL DEFAULT 0,
    "color" VARCHAR(30) NOT NULL DEFAULT 'gray',
    "type" TEXT NOT NULL DEFAULT 'aberto',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "kanban_columns_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "webhook_subscriptions" (
    "id" SERIAL NOT NULL,
    "name" VARCHAR(100) NOT NULL,
    "url" VARCHAR(2048) NOT NULL,
    "trigger_types" JSONB NOT NULL,
    "secret" TEXT NOT NULL,
    "is_active" BOOLEAN NOT NULL DEFAULT true,
    "description" TEXT,
    "created_by" VARCHAR(100),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,
    "deleted_at" TIMESTAMP(3),

    CONSTRAINT "webhook_subscriptions_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "webhook_dispatch_logs" (
    "id" SERIAL NOT NULL,
    "subscription_id" INTEGER NOT NULL,
    "event_type" TEXT NOT NULL,
    "event_entity_id" INTEGER NOT NULL,
    "attempt_number" INTEGER NOT NULL DEFAULT 1,
    "max_attempts" INTEGER NOT NULL DEFAULT 5,
    "status" TEXT NOT NULL DEFAULT 'pending',
    "payload" JSONB NOT NULL,
    "target_url" VARCHAR(2048) NOT NULL,
    "http_status" INTEGER,
    "response_body" TEXT,
    "error_message" VARCHAR(1000),
    "dispatched_at" TIMESTAMP(3),
    "responded_at" TIMESTAMP(3),
    "next_retry_at" TIMESTAMP(3),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "webhook_dispatch_logs_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "solution_templates" (
    "id" SERIAL NOT NULL,
    "title" VARCHAR(100) NOT NULL,
    "body" TEXT NOT NULL,
    "product_type" VARCHAR(20),
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "solution_templates_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "product_plan_configs" (
    "id" SERIAL NOT NULL,
    "product_type" VARCHAR(20) NOT NULL,
    "plan_name" VARCHAR(100) NOT NULL,
    "price_per_unit" DECIMAL(10,2) NOT NULL DEFAULT 0,
    "unit_label" VARCHAR(50) NOT NULL DEFAULT 'unidade',
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "product_plan_configs_pkey" PRIMARY KEY ("id")
);

-- CreateTable
CREATE TABLE "app_settings" (
    "key" VARCHAR(100) NOT NULL,
    "value" TEXT,
    "created_at" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP(3) NOT NULL,

    CONSTRAINT "app_settings_pkey" PRIMARY KEY ("key")
);

-- CreateIndex
CREATE UNIQUE INDEX "products_external_id_product_type_key" ON "products"("external_id", "product_type");

-- CreateIndex
CREATE INDEX "cards_customer_id_idx" ON "cards"("customer_id");

-- CreateIndex
CREATE INDEX "cards_product_id_idx" ON "cards"("product_id");

-- CreateIndex
CREATE INDEX "cards_status_idx" ON "cards"("status");

-- CreateIndex
CREATE UNIQUE INDEX "chat_agent_interactions_chat_id_agent_interacted_on_key" ON "chat_agent_interactions"("chat_id", "agent", "interacted_on");

-- CreateIndex
CREATE UNIQUE INDEX "tags_name_type_key" ON "tags"("name", "type");

-- CreateIndex
CREATE UNIQUE INDEX "kanban_columns_name_key" ON "kanban_columns"("name");

-- CreateIndex
CREATE INDEX "webhook_subscriptions_is_active_deleted_at_idx" ON "webhook_subscriptions"("is_active", "deleted_at");

-- CreateIndex
CREATE INDEX "webhook_dispatch_logs_subscription_id_status_idx" ON "webhook_dispatch_logs"("subscription_id", "status");

-- CreateIndex
CREATE INDEX "webhook_dispatch_logs_status_next_retry_at_idx" ON "webhook_dispatch_logs"("status", "next_retry_at");

-- CreateIndex
CREATE INDEX "webhook_dispatch_logs_event_type_event_entity_id_idx" ON "webhook_dispatch_logs"("event_type", "event_entity_id");

-- AddForeignKey
ALTER TABLE "products" ADD CONSTRAINT "products_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "product_changes" ADD CONSTRAINT "product_changes_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "product_changes" ADD CONSTRAINT "product_changes_product_id_fkey" FOREIGN KEY ("product_id") REFERENCES "products"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "cards" ADD CONSTRAINT "cards_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "cards" ADD CONSTRAINT "cards_product_id_fkey" FOREIGN KEY ("product_id") REFERENCES "products"("id") ON DELETE SET NULL ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "chats" ADD CONSTRAINT "chats_ombudsman_card_id_fkey" FOREIGN KEY ("ombudsman_card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "chat_agent_interactions" ADD CONSTRAINT "chat_agent_interactions_chat_id_fkey" FOREIGN KEY ("chat_id") REFERENCES "chats"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "card_comments" ADD CONSTRAINT "card_comments_card_id_fkey" FOREIGN KEY ("card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "card_activity_logs" ADD CONSTRAINT "card_activity_logs_card_id_fkey" FOREIGN KEY ("card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "related_cards" ADD CONSTRAINT "related_cards_card_id_fkey" FOREIGN KEY ("card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "related_cards" ADD CONSTRAINT "related_cards_related_card_id_fkey" FOREIGN KEY ("related_card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "customer_tag" ADD CONSTRAINT "customer_tag_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "customer_tag" ADD CONSTRAINT "customer_tag_tag_id_fkey" FOREIGN KEY ("tag_id") REFERENCES "tags"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "card_tag" ADD CONSTRAINT "card_tag_card_id_fkey" FOREIGN KEY ("card_id") REFERENCES "cards"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "card_tag" ADD CONSTRAINT "card_tag_tag_id_fkey" FOREIGN KEY ("tag_id") REFERENCES "tags"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE "webhook_dispatch_logs" ADD CONSTRAINT "webhook_dispatch_logs_subscription_id_fkey" FOREIGN KEY ("subscription_id") REFERENCES "webhook_subscriptions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
