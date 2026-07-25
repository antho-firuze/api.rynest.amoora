CREATE TABLE public.users (
	id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
	id2 bigint,
	"name" varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	email_verified_at timestamp(0) NULL,
	"password" varchar(255) NOT NULL,
	remember_token varchar(100) NULL,
	verify_code varchar(100) NULL,
	"role" varchar(255) NULL,
	phone varchar(255) NULL,
	avatar varchar(255) NULL,
	nik varchar(255) NULL,
	is_active bool DEFAULT true NOT NULL,
	created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
	updated_at TIMESTAMP WITH TIME ZONE
);

-- Default user admin@amooratravel.com | amooratravel
INSERT INTO public.users (id,id2,"name",email,email_verified_at,"password",remember_token,verify_code,"role",phone,avatar,nik,is_active,created_at,updated_at) VALUES
	 ('e6126b2f-bae5-4edf-ba7c-4e1d78cfb11b'::uuid,NULL,'Admin','admin@amooratravel.com',NULL,'40050bee3a86d7ebbb19ce148879c6fe',NULL,NULL,'jamaah',NULL,NULL,NULL,true,'2026-07-21 08:10:45.368227+07',NULL);


-- DROP TABLE IF EXISTS public.users CASCADE;
	
CREATE TABLE public.notifications (
	id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
	topic varchar(255) NULL,
	title varchar(255) NULL,
	body varchar(255) NULL,
	image TEXT NULL,
	payload JSONB, -- Flexible storage for deep-linking (e.g., {"screen": "order_details", "id": "123"}),	
	publish_at TIMESTAMP WITH TIME ZONE,
	is_active BOOLEAN DEFAULT TRUE,
	target_type varchar(20),
	device_id varchar(255),
	user_id UUID,
	created_by UUID,
	created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_notifications_topic ON public.notifications(topic);
CREATE INDEX idx_notifications_target_type ON public.notifications(target_type);
CREATE INDEX idx_notifications_publish ON public.notifications(publish_at);
CREATE INDEX idx_notifications_active ON public.notifications(topic) WHERE is_active = TRUE;

-- DROP TABLE IF EXISTS public.notifications CASCADE;

CREATE TABLE public.user_notifications (
	notification_id UUID REFERENCES public.notifications(id) ON DELETE CASCADE,
	device_id varchar(255),
	user_id UUID,
	is_read BOOLEAN DEFAULT FALSE,
	is_deleted BOOLEAN DEFAULT FALSE,
	is_archived BOOLEAN DEFAULT FALSE,
	updated_at TIMESTAMP WITH TIME ZONE,
	CONSTRAINT unique_notification_device_user UNIQUE (notification_id, device_id, user_id)
);

-- DROP TABLE IF EXISTS public.user_notifications CASCADE;

CREATE TABLE public.devices (
	id varchar(255) PRIMARY KEY NOT NULL,
	"name" varchar(255) NOT NULL,
	platform varchar(10) NOT NULL,
	user_id bigint NULL,
	created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
	updated_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_devices_platform ON public.devices(platform);

-- DROP TABLE IF EXISTS public.devices CASCADE;