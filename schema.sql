
  CREATE TABLE IF NOT EXISTS users (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE COLLATE NOCASE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'reader'
      CHECK (role IN ('reader', 'subscriber', 'writer', 'moderator', 'admin')),
    subscription TEXT NOT NULL DEFAULT 'Free',
    status TEXT NOT NULL DEFAULT 'active'
      CHECK (status IN ('active', 'suspended', 'deleted')),
    email_verified INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    last_login_at TEXT
  );

  CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    created_at TEXT NOT NULL,
    last_seen_at TEXT NOT NULL,
    ip_address TEXT,
    user_agent TEXT
  );

  CREATE TABLE IF NOT EXISTS login_attempts (
    key TEXT PRIMARY KEY,
    attempts INTEGER NOT NULL DEFAULT 0,
    first_attempt_at TEXT NOT NULL,
    blocked_until TEXT
  );

  CREATE TABLE IF NOT EXISTS audit_logs (
    id TEXT PRIMARY KEY,
    actor_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    action TEXT NOT NULL,
    target_type TEXT,
    target_id TEXT,
    ip_address TEXT,
    metadata TEXT,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS platform_documents (
    key TEXT PRIMARY KEY,
    value_json TEXT NOT NULL,
    updated_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS user_documents (
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    key TEXT NOT NULL,
    value_json TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    PRIMARY KEY (user_id, key)
  );

  CREATE TABLE IF NOT EXISTS engagement_events (
    id TEXT PRIMARY KEY,
    user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    anonymous_id TEXT,
    story_slug TEXT,
    event_type TEXT NOT NULL,
    value REAL,
    metadata TEXT NOT NULL DEFAULT '{}',
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS recommendation_profiles (
    user_id TEXT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    topic_weights_json TEXT NOT NULL DEFAULT '{}',
    tag_weights_json TEXT NOT NULL DEFAULT '{}',
    author_weights_json TEXT NOT NULL DEFAULT '{}',
    story_weights_json TEXT NOT NULL DEFAULT '{}',
    hidden_topics_json TEXT NOT NULL DEFAULT '[]',
    hidden_authors_json TEXT NOT NULL DEFAULT '[]',
    hidden_stories_json TEXT NOT NULL DEFAULT '[]',
    model_version INTEGER NOT NULL DEFAULT 1,
    signals_count INTEGER NOT NULL DEFAULT 0,
    last_trained_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS recommendation_story_scores (
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    story_slug TEXT NOT NULL,
    score REAL NOT NULL,
    reason TEXT NOT NULL,
    factors_json TEXT NOT NULL DEFAULT '[]',
    model_version INTEGER NOT NULL DEFAULT 1,
    computed_at TEXT NOT NULL,
    PRIMARY KEY (user_id, story_slug)
  );

  CREATE TABLE IF NOT EXISTS comments (
    id TEXT PRIMARY KEY,
    story_slug TEXT NOT NULL,
    parent_id TEXT REFERENCES comments(id) ON DELETE CASCADE,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    text TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'published'
      CHECK (status IN ('published', 'reported', 'hidden', 'deleted')),
    pinned INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS comment_likes (
    comment_id TEXT NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TEXT NOT NULL,
    PRIMARY KEY (comment_id, user_id)
  );

  CREATE TABLE IF NOT EXISTS story_translations (
    story_slug TEXT NOT NULL,
    locale TEXT NOT NULL,
    title TEXT NOT NULL,
    dek TEXT NOT NULL,
    body_json TEXT NOT NULL DEFAULT '[]',
    content_html TEXT NOT NULL DEFAULT '',
    seo_json TEXT NOT NULL DEFAULT '{}',
    status TEXT NOT NULL DEFAULT 'ready'
      CHECK (status IN ('pending', 'ready', 'failed')),
    source_hash TEXT NOT NULL,
    provider TEXT NOT NULL DEFAULT 'openai',
    error TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    PRIMARY KEY (story_slug, locale)
  );

  CREATE VIRTUAL TABLE IF NOT EXISTS story_search_index USING fts5(
    slug UNINDEXED,
    title,
    dek,
    author,
    publication,
    topic,
    tags,
    body,
    tokenize='unicode61 remove_diacritics 2'
  );

  CREATE TABLE IF NOT EXISTS subscriptions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan_id TEXT NOT NULL,
    provider TEXT NOT NULL,
    provider_subscription_id TEXT,
    currency TEXT NOT NULL DEFAULT 'INR',
    amount INTEGER NOT NULL,
    status TEXT NOT NULL
      CHECK (status IN ('pending', 'active', 'past_due', 'cancelled', 'expired', 'failed')),
    starts_at TEXT,
    ends_at TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS payments (
    id TEXT PRIMARY KEY,
    user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    subscription_id TEXT REFERENCES subscriptions(id) ON DELETE SET NULL,
    provider TEXT NOT NULL,
    provider_order_id TEXT,
    provider_payment_id TEXT,
    purpose TEXT NOT NULL,
    amount INTEGER NOT NULL,
    currency TEXT NOT NULL,
    status TEXT NOT NULL
      CHECK (status IN ('created', 'pending', 'paid', 'failed', 'refunded')),
    metadata TEXT NOT NULL DEFAULT '{}',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS writer_tips (
    id TEXT PRIMARY KEY,
    payment_id TEXT REFERENCES payments(id) ON DELETE SET NULL,
    sender_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    story_slug TEXT NOT NULL,
    writer_name TEXT NOT NULL,
    gross_amount INTEGER NOT NULL,
    platform_fee INTEGER NOT NULL,
    writer_amount INTEGER NOT NULL,
    currency TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending'
      CHECK (status IN ('pending', 'paid', 'failed', 'refunded')),
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS payout_accounts (
    user_id TEXT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    account_holder TEXT NOT NULL,
    bank_name TEXT NOT NULL,
    payout_method TEXT NOT NULL DEFAULT 'bank',
    account_reference TEXT NOT NULL,
    tax_id_reference TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'pending_review'
      CHECK (status IN ('pending_review', 'verified', 'rejected')),
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS writer_payouts (
    id TEXT PRIMARY KEY,
    writer_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    amount INTEGER NOT NULL,
    currency TEXT NOT NULL DEFAULT 'INR',
    status TEXT NOT NULL DEFAULT 'pending'
      CHECK (status IN ('pending', 'processing', 'paid', 'failed', 'cancelled')),
    payout_account_snapshot TEXT NOT NULL DEFAULT '{}',
    period_start TEXT,
    period_end TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    paid_at TEXT
  );

  CREATE TABLE IF NOT EXISTS writer_payout_items (
    payout_id TEXT NOT NULL REFERENCES writer_payouts(id) ON DELETE CASCADE,
    tip_id TEXT NOT NULL REFERENCES writer_tips(id) ON DELETE CASCADE,
    amount INTEGER NOT NULL,
    PRIMARY KEY (payout_id, tip_id)
  );

  CREATE TABLE IF NOT EXISTS gift_memberships (
    id TEXT PRIMARY KEY,
    payment_id TEXT REFERENCES payments(id) ON DELETE SET NULL,
    purchaser_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    recipient_email TEXT NOT NULL,
    plan_id TEXT NOT NULL,
    months INTEGER NOT NULL,
    message TEXT NOT NULL DEFAULT '',
    deliver_at TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending'
      CHECK (status IN ('pending', 'paid', 'redeemed', 'cancelled', 'failed')),
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS discount_codes (
    id TEXT PRIMARY KEY,
    code TEXT NOT NULL UNIQUE COLLATE NOCASE,
    description TEXT NOT NULL DEFAULT '',
    discount_type TEXT NOT NULL CHECK (discount_type IN ('percent', 'amount')),
    discount_value INTEGER NOT NULL,
    applies_to_plan_id TEXT,
    audience TEXT NOT NULL DEFAULT 'All readers',
    max_redemptions INTEGER,
    redeemed_count INTEGER NOT NULL DEFAULT 0,
    starts_at TEXT,
    ends_at TEXT,
    active INTEGER NOT NULL DEFAULT 1,
    created_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS discount_redemptions (
    id TEXT PRIMARY KEY,
    discount_id TEXT NOT NULL REFERENCES discount_codes(id) ON DELETE CASCADE,
    user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    payment_id TEXT REFERENCES payments(id) ON DELETE SET NULL,
    amount_discounted INTEGER NOT NULL,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS support_tickets (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    subject TEXT NOT NULL,
    category TEXT NOT NULL,
    priority TEXT NOT NULL DEFAULT 'Normal',
    status TEXT NOT NULL DEFAULT 'Open',
    owner TEXT NOT NULL DEFAULT 'Support',
    details TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS account_deletion_requests (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status TEXT NOT NULL DEFAULT 'pending_verification',
    requested_at TEXT NOT NULL,
    scheduled_for TEXT NOT NULL,
    cancelled_at TEXT
  );

  CREATE TABLE IF NOT EXISTS push_subscriptions (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    endpoint TEXT NOT NULL UNIQUE,
    subscription_json TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS media_assets (
    id TEXT PRIMARY KEY,
    uploader_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    original_name TEXT NOT NULL,
    stored_name TEXT NOT NULL,
    url TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    size_bytes INTEGER NOT NULL,
    width INTEGER,
    height INTEGER,
    alt_text TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS payment_webhooks (
    id TEXT PRIMARY KEY,
    provider TEXT NOT NULL,
    event_type TEXT NOT NULL,
    payment_id TEXT,
    provider_payment_id TEXT,
    signature_valid INTEGER NOT NULL DEFAULT 0,
    payload_json TEXT NOT NULL,
    processed_at TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'processed'
      CHECK (status IN ('processed', 'ignored', 'failed'))
  );

  CREATE TABLE IF NOT EXISTS security_tokens (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL CHECK (type IN ('email_verification', 'password_recovery')),
    expires_at TEXT NOT NULL,
    consumed_at TEXT,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS user_security_settings (
    user_id TEXT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    two_factor_secret TEXT,
    two_factor_enabled INTEGER NOT NULL DEFAULT 0,
    recovery_codes_json TEXT NOT NULL DEFAULT '[]',
    login_alerts_enabled INTEGER NOT NULL DEFAULT 0,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS ad_campaigns (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    placement TEXT NOT NULL CHECK (placement IN ('leaderboard', 'square', 'native')),
    sponsor TEXT NOT NULL,
    headline TEXT NOT NULL,
    target_url TEXT NOT NULL,
    creative_url TEXT NOT NULL DEFAULT '',
    target_topics TEXT NOT NULL DEFAULT '[]',
    status TEXT NOT NULL DEFAULT 'active'
      CHECK (status IN ('draft', 'active', 'paused', 'ended')),
    budget INTEGER NOT NULL DEFAULT 0,
    cpm INTEGER NOT NULL DEFAULT 0,
    created_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS ad_events (
    id TEXT PRIMARY KEY,
    campaign_id TEXT NOT NULL REFERENCES ad_campaigns(id) ON DELETE CASCADE,
    user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    event_type TEXT NOT NULL CHECK (event_type IN ('impression', 'click')),
    placement TEXT NOT NULL,
    story_slug TEXT,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS social_accounts (
    provider TEXT NOT NULL,
    provider_user_id TEXT NOT NULL,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    email TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    PRIMARY KEY (provider, provider_user_id)
  );

  CREATE TABLE IF NOT EXISTS oauth_states (
    state TEXT PRIMARY KEY,
    provider TEXT NOT NULL,
    redirect_uri TEXT NOT NULL,
    expires_at TEXT NOT NULL,
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS moderation_cases (
    id TEXT PRIMARY KEY,
    reporter_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    target_type TEXT NOT NULL,
    target_id TEXT NOT NULL,
    kind TEXT NOT NULL,
    subject TEXT NOT NULL,
    risk TEXT NOT NULL DEFAULT 'Medium',
    status TEXT NOT NULL DEFAULT 'Open',
    assignee_user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    evidence_json TEXT NOT NULL DEFAULT '[]',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS provider_credentials (
    provider TEXT NOT NULL,
    key TEXT NOT NULL,
    value_encrypted TEXT NOT NULL,
    environment TEXT NOT NULL DEFAULT 'production',
    enabled INTEGER NOT NULL DEFAULT 1,
    updated_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    updated_at TEXT NOT NULL,
    PRIMARY KEY (provider, key)
  );

  CREATE TABLE IF NOT EXISTS subscription_events (
    id TEXT PRIMARY KEY,
    subscription_id TEXT REFERENCES subscriptions(id) ON DELETE SET NULL,
    user_id TEXT REFERENCES users(id) ON DELETE SET NULL,
    provider TEXT NOT NULL,
    event_type TEXT NOT NULL,
    status_before TEXT,
    status_after TEXT,
    payload_json TEXT NOT NULL DEFAULT '{}',
    created_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS subscription_dunning (
    id TEXT PRIMARY KEY,
    subscription_id TEXT REFERENCES subscriptions(id) ON DELETE CASCADE,
    user_id TEXT REFERENCES users(id) ON DELETE CASCADE,
    status TEXT NOT NULL DEFAULT 'open'
      CHECK (status IN ('open', 'notified', 'recovered', 'cancelled', 'closed')),
    attempts INTEGER NOT NULL DEFAULT 0,
    next_attempt_at TEXT,
    last_error TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS payout_transfers (
    id TEXT PRIMARY KEY,
    payout_id TEXT NOT NULL REFERENCES writer_payouts(id) ON DELETE CASCADE,
    provider TEXT NOT NULL,
    provider_transfer_id TEXT,
    status TEXT NOT NULL DEFAULT 'queued'
      CHECK (status IN ('queued', 'processing', 'paid', 'failed', 'cancelled')),
    request_json TEXT NOT NULL DEFAULT '{}',
    response_json TEXT NOT NULL DEFAULT '{}',
    error TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS background_jobs (
    id TEXT PRIMARY KEY,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'queued'
      CHECK (status IN ('queued', 'running', 'completed', 'failed', 'cancelled')),
    payload_json TEXT NOT NULL DEFAULT '{}',
    attempts INTEGER NOT NULL DEFAULT 0,
    available_at TEXT NOT NULL,
    locked_at TEXT,
    last_error TEXT NOT NULL DEFAULT '',
    result_json TEXT NOT NULL DEFAULT '{}',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS media_variants (
    id TEXT PRIMARY KEY,
    media_asset_id TEXT NOT NULL REFERENCES media_assets(id) ON DELETE CASCADE,
    variant TEXT NOT NULL,
    url TEXT NOT NULL,
    mime_type TEXT NOT NULL,
    width INTEGER,
    height INTEGER,
    size_bytes INTEGER NOT NULL,
    created_at TEXT NOT NULL,
    UNIQUE(media_asset_id, variant)
  );

  CREATE TABLE IF NOT EXISTS security_policies (
    key TEXT PRIMARY KEY,
    value_json TEXT NOT NULL,
    updated_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS role_permissions (
    role TEXT NOT NULL,
    permission TEXT NOT NULL,
    allowed INTEGER NOT NULL DEFAULT 1,
    updated_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    updated_at TEXT NOT NULL,
    PRIMARY KEY (role, permission)
  );

  CREATE TABLE IF NOT EXISTS passkey_credentials (
    id TEXT PRIMARY KEY,
    user_id TEXT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    credential_id TEXT NOT NULL UNIQUE,
    public_key TEXT NOT NULL,
    sign_count INTEGER NOT NULL DEFAULT 0,
    transports TEXT NOT NULL DEFAULT '[]',
    created_at TEXT NOT NULL,
    last_used_at TEXT
  );

  CREATE TABLE IF NOT EXISTS moderation_rules (
    id TEXT PRIMARY KEY,
    kind TEXT NOT NULL CHECK (kind IN ('blocked_word', 'link_filter', 'bot_score', 'duplicate_check', 'plagiarism')),
    pattern TEXT NOT NULL,
    action TEXT NOT NULL DEFAULT 'queue'
      CHECK (action IN ('allow', 'queue', 'hide', 'block')),
    enabled INTEGER NOT NULL DEFAULT 1,
    created_by TEXT REFERENCES users(id) ON DELETE SET NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS pwa_sync_queue (
    id TEXT PRIMARY KEY,
    user_id TEXT REFERENCES users(id) ON DELETE CASCADE,
    client_id TEXT,
    type TEXT NOT NULL,
    payload_json TEXT NOT NULL DEFAULT '{}',
    status TEXT NOT NULL DEFAULT 'queued'
      CHECK (status IN ('queued', 'processed', 'failed')),
    created_at TEXT NOT NULL,
    processed_at TEXT
  );

  CREATE TABLE IF NOT EXISTS system_health_checks (
    key TEXT PRIMARY KEY,
    status TEXT NOT NULL,
    details_json TEXT NOT NULL DEFAULT '{}',
    checked_at TEXT NOT NULL
  );

  CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
  CREATE INDEX IF NOT EXISTS idx_sessions_expires_at ON sessions(expires_at);
  CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status);
  CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_user_documents_user ON user_documents(user_id);
  CREATE INDEX IF NOT EXISTS idx_engagement_user_created ON engagement_events(user_id, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_engagement_story_type ON engagement_events(story_slug, event_type);
  CREATE INDEX IF NOT EXISTS idx_recommendation_scores_user_score ON recommendation_story_scores(user_id, score DESC);
  CREATE INDEX IF NOT EXISTS idx_comments_story_created ON comments(story_slug, created_at);
  CREATE INDEX IF NOT EXISTS idx_story_translations_locale ON story_translations(locale, status);
  CREATE INDEX IF NOT EXISTS idx_subscriptions_user_status ON subscriptions(user_id, status);
  CREATE INDEX IF NOT EXISTS idx_payments_user_created ON payments(user_id, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_writer_tips_writer_status ON writer_tips(writer_name, status);
  CREATE INDEX IF NOT EXISTS idx_payouts_writer_status ON writer_payouts(writer_user_id, status);
  CREATE INDEX IF NOT EXISTS idx_gifts_recipient_status ON gift_memberships(recipient_email, status);
  CREATE INDEX IF NOT EXISTS idx_discount_codes_active ON discount_codes(active, code);
  CREATE INDEX IF NOT EXISTS idx_tickets_user_created ON support_tickets(user_id, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_moderation_status_created ON moderation_cases(status, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_security_tokens_user_type ON security_tokens(user_id, type, expires_at);
  CREATE INDEX IF NOT EXISTS idx_ad_campaigns_placement_status ON ad_campaigns(placement, status);
  CREATE INDEX IF NOT EXISTS idx_ad_events_campaign_type ON ad_events(campaign_id, event_type);
  CREATE INDEX IF NOT EXISTS idx_media_assets_created ON media_assets(created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_payment_webhooks_provider ON payment_webhooks(provider, processed_at DESC);
  CREATE INDEX IF NOT EXISTS idx_provider_credentials_provider ON provider_credentials(provider, enabled);
  CREATE INDEX IF NOT EXISTS idx_subscription_events_subscription ON subscription_events(subscription_id, created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_dunning_status_next ON subscription_dunning(status, next_attempt_at);
  CREATE INDEX IF NOT EXISTS idx_payout_transfers_payout ON payout_transfers(payout_id, status);
  CREATE INDEX IF NOT EXISTS idx_background_jobs_status_available ON background_jobs(status, available_at);
  CREATE INDEX IF NOT EXISTS idx_media_variants_asset ON media_variants(media_asset_id);
  CREATE INDEX IF NOT EXISTS idx_moderation_rules_kind ON moderation_rules(kind, enabled);
  CREATE INDEX IF NOT EXISTS idx_pwa_sync_user_status ON pwa_sync_queue(user_id, status);
