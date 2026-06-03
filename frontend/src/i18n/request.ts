import { notFound } from "next/navigation";
import { getRequestConfig } from "next-intl/server";

export const locales = ["en", "ar"] as const;
export const defaultLocale = "en" as const;
export type Locale = (typeof locales)[number];

export default getRequestConfig(async ({ requestLocale }) => {
  const requested = await requestLocale;
  const locale = locales.includes(requested as Locale) ? (requested as Locale) : defaultLocale;

  try {
    return {
      locale,
      messages: (await import(`./locales/${locale}.json`)).default,
    };
  } catch (error) {
    notFound();
  }
});
