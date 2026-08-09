import { defineEventHandler } from 'h3'
import { identityHostedResendVerification } from '../../../utils/identity-hosted-auth'

export default defineEventHandler(async event => await identityHostedResendVerification(event))
