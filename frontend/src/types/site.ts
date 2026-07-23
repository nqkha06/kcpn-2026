export interface SiteAppearance {
  logo_light: string | null;
  logo_dark: string | null;
  favicon: string | null;
  social_image: string | null;
  site_name: string;
  site_title: string;
  tagline: string | null;
  meta_description: string | null;
}

export interface SiteMenuItem {
  id: number;
  title: string;
  url: string;
  target: string | null;
  canonical: string;
  children: SiteMenuItem[];
}

export interface PublicSiteConfiguration {
  locale: string;
  default_locale: string;
  locales: string[];
  can_register: boolean;
  appearance: SiteAppearance;
  menus: {
    home_header: SiteMenuItem[];
    home_footer: SiteMenuItem[];
    user_header: SiteMenuItem[];
  };
}

export interface PublicPage {
  id: number;
  title: string;
  slug: string;
  image: string | null;
  content: string | null;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  updated_at: string | null;
}
