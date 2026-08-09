import { defineEventHandler } from 'h3'
import { identityHostedFlowContext } from '../../utils/identity-hosted-auth'

export default defineEventHandler(async event => await identityHostedFlowContext(event))
