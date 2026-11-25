FROM node:20-alpine

WORKDIR /app

COPY package*.json ./

RUN npm ci --omit=dev || npm install --omit=dev

COPY src/ ./src/

EXPOSE 4001

HEALTHCHECK --interval=15s --timeout=5s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost:4001/health || exit 1

CMD ["node", "src/server.js"]

