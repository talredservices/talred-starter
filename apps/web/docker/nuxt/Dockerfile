FROM node:24-alpine AS build

WORKDIR /app
RUN corepack enable

COPY apps/web/package.json apps/web/pnpm-lock.yaml apps/web/pnpm-workspace.yaml ./
COPY apps/web/packages ./packages
COPY apps/web/patches ./patches
RUN pnpm install --frozen-lockfile

COPY apps/web/ ./
COPY integrations/identity-hosted-auth/brand /integrations/identity-hosted-auth/brand
RUN pnpm build

FROM node:24-alpine

WORKDIR /app
ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=3000

COPY --from=build /app/.output ./.output

CMD ["node", ".output/server/index.mjs"]
