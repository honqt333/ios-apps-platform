import createMiddleware from "next-intl/middleware";
import { locales, defaultLocale } from "./request";

export default createMiddleware({
  locales,
  defaultLocale,
  localePrefix: "always",
});

export const config = {
  matcher: ["/((?!api|_next|.*\\..*).*)"],
};
