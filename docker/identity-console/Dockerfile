FROM node:24-alpine AS build

WORKDIR /app
RUN corepack enable

# Compose supplies a content-addressed checkout of the public Identity source.
COPY --from=identity / /app/
COPY integrations/identity-hosted-auth/ /app/consumer-integrations/starter/

ARG IDENTITY_HOSTED_AUTH_INTEGRATIONS=/app/consumer-integrations/starter
ARG IDENTITY_HOSTED_AUTH_PAGE_SETS=default,starter,saas,projects
ARG TALRED_IDENTITY_RELEASE
ENV IDENTITY_HOSTED_AUTH_INTEGRATIONS=${IDENTITY_HOSTED_AUTH_INTEGRATIONS}
ENV IDENTITY_HOSTED_AUTH_PAGE_SETS=${IDENTITY_HOSTED_AUTH_PAGE_SETS}

RUN pnpm install --frozen-lockfile && pnpm build

FROM node:24-alpine

WORKDIR /app
ARG TALRED_IDENTITY_RELEASE
ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=3000

LABEL org.talred.identity.source-release=${TALRED_IDENTITY_RELEASE}
COPY --from=build /app/.output ./.output

CMD ["node", ".output/server/index.mjs"]
