/* ---------------------------------------------------------------------------------------*/
/*  @file:    startup_MKM14Z5.s                                                           */
/*  @purpose: CMSIS Cortex-M0P Core Device Startup File                                   */
/*            MKM14Z5                                                                     */
/*  @version: 1.3                                                                         */
/*  @date:    2014-6-25                                                                   */
/*  @build:   b140626                                                                     */
/* ---------------------------------------------------------------------------------------*/
/*                                                                                        */
/* Copyright (c) 1997 - 2014 , Freescale Semiconductor, Inc.                              */
/* All rights reserved.                                                                   */
/*                                                                                        */
/* Redistribution and use in source and binary forms, with or without modification,       */
/* are permitted provided that the following conditions are met:                          */
/*                                                                                        */
/* o Redistributions of source code must retain the above copyright notice, this list     */
/*   of conditions and the following disclaimer.                                          */
/*                                                                                        */
/* o Redistributions in binary form must reproduce the above copyright notice, this       */
/*   list of conditions and the following disclaimer in the documentation and/or          */
/*   other materials provided with the distribution.                                      */
/*                                                                                        */
/* o Neither the name of Freescale Semiconductor, Inc. nor the names of its               */
/*   contributors may be used to endorse or promote products derived from this            */
/*   software without specific prior written permission.                                  */
/*                                                                                        */
/* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND        */
/* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED          */
/* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE                 */
/* DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR       */
/* ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES         */
/* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;           */
/* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON         */
/* ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT                */
/* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS          */
/* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.                           */
/*****************************************************************************/
/* Version: CodeSourcery Sourcery G++ Lite (with CS3)                        */
/*****************************************************************************/


/*
// <h> Stack Configuration
//   <o> Stack Size (in Bytes) <0x0-0xFFFFFFFF:8>
// </h>
*/

    .equ    Stack_Size, 0x00000100
    .section ".stack", "w"
    .align  3
    .globl  __cs3_stack_mem
    .globl  __cs3_stack_size
__cs3_stack_mem:
    .if     Stack_Size
    .space  Stack_Size
    .endif
    .size   __cs3_stack_mem,  . - __cs3_stack_mem
    .set    __cs3_stack_size, . - __cs3_stack_mem


/*
// <h> Heap Configuration
//   <o>  Heap Size (in Bytes) <0x0-0xFFFFFFFF:8>
// </h>
*/

    .equ    Heap_Size,  0x00001000

    .section ".heap", "w"
    .align  3
    .globl  __cs3_heap_start
    .globl  __cs3_heap_end
__cs3_heap_start:
    .if     Heap_Size
    .space  Heap_Size
    .endif
__cs3_heap_end:


/* Vector Table */

    .section ".cs3.interrupt_vector"
    .globl  __cs3_interrupt_vector_cortex_m
    .type   __cs3_interrupt_vector_cortex_m, %object

__cs3_interrupt_vector_cortex_m:
    .long   __cs3_stack                 /* Top of Stack                 */
    .long   __cs3_reset                 /* Reset Handler                */
    .long   NMI_Handler                                     /* NMI Handler*/
    .long   HardFault_Handler                               /* Hard Fault Handler*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   SVC_Handler                                     /* SVCall Handler*/
    .long   0                                               /* Reserved*/
    .long   0                                               /* Reserved*/
    .long   PendSV_Handler                                  /* PendSV Handler*/
    .long   SysTick_Handler                                 /* SysTick Handler*/

                                                            /* External Interrupts*/
    .long   DMA0_IRQHandler                                 /* DMA channel 0 transfer complete*/
    .long   DMA1_IRQHandler                                 /* DMA channel 1 transfer complete*/
    .long   DMA2_IRQHandler                                 /* DMA channel 2 transfer complete*/
    .long   DMA3_IRQHandler                                 /* DMA channel 3 transfer complete*/
    .long   SPI0_IRQHandler                                 /* SPI0 ORed interrupt*/
    .long   SPI1_IRQHandler                                 /* SPI1 ORed interrupt*/
    .long   PMC_IRQHandler                                  /* Low-voltage detect, low-voltage warning*/
    .long   TMR0_IRQHandler                                 /* Quad Timer Channel 0*/
    .long   TMR1_IRQHandler                                 /* Quad Timer Channel 1*/
    .long   TMR2_IRQHandler                                 /* Quad Timer Channel 2*/
    .long   TMR3_IRQHandler                                 /* Quad Timer Channel 3*/
    .long   PIT0_PIT1_IRQHandler                            /* PIT0/PIT1 ORed interrupt*/
    .long   LLWU_IRQHandler                                 /* Low Leakage Wakeup*/
    .long   FTFA_IRQHandler                                 /* Command complete and read collision*/
    .long   CMP0_CMP1_IRQHandler                            /* CMP0/CMP1 ORed interrupt*/
    .long   LCD_IRQHandler                                  /* LCD interrupt*/
    .long   ADC_IRQHandler                                  /* ADC interrupt*/
    .long   PTx_IRQHandler                                  /* Single interrupt vector for GPIOA,GPIOB,GPIOC,GPIOD,GPIOE,GPIOF,GPIOG,GPIOH,GPIOI*/
    .long   RNGA_IRQHandler                                 /* RNGA interrupt*/
    .long   UART0_UART1_IRQHandler                          /* UART0/UART1 ORed interrupt*/
    .long   UART2_UART3_IRQHandler                          /* UART2/UART3 ORed interrupt*/
    .long   AFE_CH0_IRQHandler                              /* AFE Channel 0*/
    .long   AFE_CH1_IRQHandler                              /* AFE Channel 1*/
    .long   AFE_CH2_IRQHandler                              /* AFE Channel 2*/
    .long   AFE_CH3_IRQHandler                              /* AFE Channel 3*/
    .long   RTC_IRQHandler                                  /* IRTC interrupt*/
    .long   I2C0_I2C1_IRQHandler                            /* I2C0/I2C1 ORed interrupt*/
    .long   EWM_IRQHandler                                  /* EWM interrupt*/
    .long   MCG_IRQHandler                                  /* MCG interrupt*/
    .long   Watchdog_IRQHandler                             /* WDOG interrupt*/
    .long   LPTMR_IRQHandler                                /* LPTMR interrupt*/
    .long   XBAR_IRQHandler                                 /* XBAR interrupt*/

    .size   __cs3_interrupt_vector_cortex_m, . - __cs3_interrupt_vector_cortex_m

/* Flash Configuration */
    .org    0x400,0xFF              /* advance the section counter to the flash config area */

    .long 0xFFFFFFFF
    .long 0xFFFFFFFF
    .long 0xFFFFFFFF
    .long 0xFFFFFFFE

    .thumb

/* Reset Handler */

    .section .cs3.reset,"x",%progbits
    .thumb_func
    .globl  __cs3_reset_cortex_m
    .type   __cs3_reset_cortex_m, %function
__cs3_reset_cortex_m:
    .fnstart
    LDR     R0, =SystemInit
    BLX     R0
    LDR     R0,=_start
    BX      R0
    .pool
    .cantunwind
    .fnend
    .size   __cs3_reset_cortex_m,.-__cs3_reset_cortex_m

    .section ".text"
    .weak  Default_Handler
    .type   Default_Handler, %function
Default_Handler:
    B       .
    .size   Default_Handler, . - Default_Handler

/*    Macro to define default handlers. Default handler
 *    will be weak symbol and just dead loops. They can be
 *    overwritten by other handlers */
  .macro	def_irq_handler	handler_name
  .weak	\handler_name
  .set	\handler_name, Default_Handler
  .endm

/* Exception Handlers */
    def_irq_handler    NMI_Handler
    def_irq_handler    HardFault_Handler
    def_irq_handler    SVC_Handler
    def_irq_handler    PendSV_Handler
    def_irq_handler    SysTick_Handler
    def_irq_handler    DMA0_IRQHandler
    def_irq_handler    DMA1_IRQHandler
    def_irq_handler    DMA2_IRQHandler
    def_irq_handler    DMA3_IRQHandler
    def_irq_handler    SPI0_IRQHandler
    def_irq_handler    SPI1_IRQHandler
    def_irq_handler    PMC_IRQHandler
    def_irq_handler    TMR0_IRQHandler
    def_irq_handler    TMR1_IRQHandler
    def_irq_handler    TMR2_IRQHandler
    def_irq_handler    TMR3_IRQHandler
    def_irq_handler    PIT0_PIT1_IRQHandler
    def_irq_handler    LLWU_IRQHandler
    def_irq_handler    FTFA_IRQHandler
    def_irq_handler    CMP0_CMP1_IRQHandler
    def_irq_handler    LCD_IRQHandler
    def_irq_handler    ADC_IRQHandler
    def_irq_handler    PTx_IRQHandler
    def_irq_handler    RNGA_IRQHandler
    def_irq_handler    UART0_UART1_IRQHandler
    def_irq_handler    UART2_UART3_IRQHandler
    def_irq_handler    AFE_CH0_IRQHandler
    def_irq_handler    AFE_CH1_IRQHandler
    def_irq_handler    AFE_CH2_IRQHandler
    def_irq_handler    AFE_CH3_IRQHandler
    def_irq_handler    RTC_IRQHandler
    def_irq_handler    I2C0_I2C1_IRQHandler
    def_irq_handler    EWM_IRQHandler
    def_irq_handler    MCG_IRQHandler
    def_irq_handler    Watchdog_IRQHandler
    def_irq_handler    LPTMR_IRQHandler
    def_irq_handler    XBAR_IRQHandler
    def_irq_handler    DefaultISR
