; * ---------------------------------------------------------------------------------------
; *  @file:    startup_MKM34Z5.s
; *  @purpose: CMSIS Cortex-M0P Core Device Startup File
; *            MKM34Z5
; *  @version: 1.6
; *  @date:    2014-6-25
; *  @build:   b140626
; * ---------------------------------------------------------------------------------------
; *
; * Copyright (c) 1997 - 2014 , Freescale Semiconductor, Inc.
; * All rights reserved.
; *
; * Redistribution and use in source and binary forms, with or without modification,
; * are permitted provided that the following conditions are met:
; *
; * o Redistributions of source code must retain the above copyright notice, this list
; *   of conditions and the following disclaimer.
; *
; * o Redistributions in binary form must reproduce the above copyright notice, this
; *   list of conditions and the following disclaimer in the documentation and/or
; *   other materials provided with the distribution.
; *
; * o Neither the name of Freescale Semiconductor, Inc. nor the names of its
; *   contributors may be used to endorse or promote products derived from this
; *   software without specific prior written permission.
; *
; * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
; * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
; * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
; * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
; * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
; * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
; * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
; * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
; * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
; * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
; *
; *------- <<< Use Configuration Wizard in Context Menu >>> ------------------
; *
; *****************************************************************************/


; <h> Stack Configuration
;   <o> Stack Size (in Bytes) <0x0-0xFFFFFFFF:8>
; </h>

Stack_Size      EQU     0x00000400

                AREA    STACK, NOINIT, READWRITE, ALIGN=3
Stack_Mem       SPACE   Stack_Size
__initial_sp


; <h> Heap Configuration
;   <o>  Heap Size (in Bytes) <0x0-0xFFFFFFFF:8>
; </h>

Heap_Size       EQU     0x00000000

                AREA    HEAP, NOINIT, READWRITE, ALIGN=3
__heap_base
Heap_Mem        SPACE   Heap_Size
__heap_limit


                PRESERVE8
                THUMB


; Vector Table Mapped to Address 0 at Reset

                AREA    RESET, DATA, READONLY
                EXPORT  __Vectors
                EXPORT  __Vectors_End
                EXPORT  __Vectors_Size

__Vectors       DCD     __initial_sp  ; Top of Stack
                DCD     Reset_Handler  ; Reset Handler
                DCD     NMI_Handler                         ;NMI Handler
                DCD     HardFault_Handler                   ;Hard Fault Handler
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     SVC_Handler                         ;SVCall Handler
                DCD     0                                   ;Reserved
                DCD     0                                   ;Reserved
                DCD     PendSV_Handler                      ;PendSV Handler
                DCD     SysTick_Handler                     ;SysTick Handler

                                                            ;External Interrupts
                DCD     DMA0_IRQHandler                     ;DMA channel 0 transfer complete
                DCD     DMA1_IRQHandler                     ;DMA channel 1 transfer complete
                DCD     DMA2_IRQHandler                     ;DMA channel 2 transfer complete
                DCD     DMA3_IRQHandler                     ;DMA channel 3 transfer complete
                DCD     SPI0_IRQHandler                     ;SPI0 ORed interrupt
                DCD     SPI1_IRQHandler                     ;SPI1 ORed interrupt
                DCD     PMC_IRQHandler                      ;Low-voltage detect, low-voltage warning
                DCD     TMR0_IRQHandler                     ;Quad Timer Channel 0
                DCD     TMR1_IRQHandler                     ;Quad Timer Channel 1
                DCD     TMR2_IRQHandler                     ;Quad Timer Channel 2
                DCD     TMR3_IRQHandler                     ;Quad Timer Channel 3
                DCD     PIT0_PIT1_IRQHandler                ;PIT0/PIT1 ORed interrupt
                DCD     LLWU_IRQHandler                     ;Low Leakage Wakeup
                DCD     FTFA_IRQHandler                     ;Command complete and read collision
                DCD     CMP0_CMP1_IRQHandler                ;CMP0/CMP1 ORed interrupt
                DCD     LCD_IRQHandler                      ;LCD interrupt
                DCD     ADC_IRQHandler                      ;ADC interrupt
                DCD     PTx_IRQHandler                      ;Single interrupt vector for GPIOA,GPIOB,GPIOC,GPIOD,GPIOE,GPIOF,GPIOG,GPIOH,GPIOI
                DCD     RNGA_IRQHandler                     ;RNGA interrupt
                DCD     UART0_UART1_IRQHandler              ;UART0/UART1 ORed interrupt
                DCD     UART2_UART3_IRQHandler              ;UART2/UART3 ORed interrupt
                DCD     AFE_CH0_IRQHandler                  ;AFE Channel 0
                DCD     AFE_CH1_IRQHandler                  ;AFE Channel 1
                DCD     AFE_CH2_IRQHandler                  ;AFE Channel 2
                DCD     AFE_CH3_IRQHandler                  ;AFE Channel 3
                DCD     RTC_IRQHandler                      ;IRTC interrupt
                DCD     I2C0_I2C1_IRQHandler                ;I2C0/I2C1 ORed interrupt
                DCD     EWM_IRQHandler                      ;EWM interrupt
                DCD     MCG_IRQHandler                      ;MCG interrupt
                DCD     Watchdog_IRQHandler                 ;WDOG interrupt
                DCD     LPTMR_IRQHandler                    ;LPTMR interrupt
                DCD     XBAR_IRQHandler                     ;XBAR interrupt
__Vectors_End

__Vectors_Size 	EQU     __Vectors_End - __Vectors

; <h> Flash Configuration
;   <i> 16-byte flash configuration field that stores default protection settings (loaded on reset)
;   <i> and security information that allows the MCU to restrict access to the FTFL module.
;   <h> Backdoor Comparison Key
;     <o0>  Backdoor Comparison Key 0.  <0x0-0xFF:2>
;     <o1>  Backdoor Comparison Key 1.  <0x0-0xFF:2>
;     <o2>  Backdoor Comparison Key 2.  <0x0-0xFF:2>
;     <o3>  Backdoor Comparison Key 3.  <0x0-0xFF:2>
;     <o4>  Backdoor Comparison Key 4.  <0x0-0xFF:2>
;     <o5>  Backdoor Comparison Key 5.  <0x0-0xFF:2>
;     <o6>  Backdoor Comparison Key 6.  <0x0-0xFF:2>
;     <o7>  Backdoor Comparison Key 7.  <0x0-0xFF:2>
BackDoorK0      EQU     0xFF
BackDoorK1      EQU     0xFF
BackDoorK2      EQU     0xFF
BackDoorK3      EQU     0xFF
BackDoorK4      EQU     0xFF
BackDoorK5      EQU     0xFF
BackDoorK6      EQU     0xFF
BackDoorK7      EQU     0xFF
;   </h>
;   <h> Program flash protection bytes (FPROT)
;     <i> Each program flash region can be protected from program and erase operation by setting the associated PROT bit.
;     <i> Each bit protects a 1/32 region of the program flash memory.
;     <h> FPROT0
;       <i> Program Flash Region Protect Register 0
;       <i> 1/32 - 8/32 region
;       <o.0>   FPROT0.0
;       <o.1>   FPROT0.1
;       <o.2>   FPROT0.2
;       <o.3>   FPROT0.3
;       <o.4>   FPROT0.4
;       <o.5>   FPROT0.5
;       <o.6>   FPROT0.6
;       <o.7>   FPROT0.7
nFPROT0         EQU     0x00
FPROT0          EQU     nFPROT0:EOR:0xFF
;     </h>
;     <h> FPROT1
;       <i> Program Flash Region Protect Register 1
;       <i> 9/32 - 16/32 region
;       <o.0>   FPROT1.0
;       <o.1>   FPROT1.1
;       <o.2>   FPROT1.2
;       <o.3>   FPROT1.3
;       <o.4>   FPROT1.4
;       <o.5>   FPROT1.5
;       <o.6>   FPROT1.6
;       <o.7>   FPROT1.7
nFPROT1         EQU     0x00
FPROT1          EQU     nFPROT1:EOR:0xFF
;     </h>
;     <h> FPROT2
;       <i> Program Flash Region Protect Register 2
;       <i> 17/32 - 24/32 region
;       <o.0>   FPROT2.0
;       <o.1>   FPROT2.1
;       <o.2>   FPROT2.2
;       <o.3>   FPROT2.3
;       <o.4>   FPROT2.4
;       <o.5>   FPROT2.5
;       <o.6>   FPROT2.6
;       <o.7>   FPROT2.7
nFPROT2         EQU     0x00
FPROT2          EQU     nFPROT2:EOR:0xFF
;     </h>
;     <h> FPROT3
;       <i> Program Flash Region Protect Register 3
;       <i> 25/32 - 32/32 region
;       <o.0>   FPROT3.0
;       <o.1>   FPROT3.1
;       <o.2>   FPROT3.2
;       <o.3>   FPROT3.3
;       <o.4>   FPROT3.4
;       <o.5>   FPROT3.5
;       <o.6>   FPROT3.6
;       <o.7>   FPROT3.7
nFPROT3         EQU     0x00
FPROT3          EQU     nFPROT3:EOR:0xFF
;     </h>
;   </h>
;   </h>
;   <h> Flash nonvolatile option byte (FOPT)
;     <i> Allows the user to customize the operation of the MCU at boot time.
;     <o.0> LPBOOT
;       <0=> Low-power boot
;       <1=> Normal boot
;     <o.2> NMI_EN
;       <0=> NMI interrupts are always blocked
;       <1=> NMI_b pin/interrupts reset default to enabled
;     <o.3> EXE_MODE
;       <0=> Execution Mode is RUN Mode
;       <1=> Execution Mode is VLPR Mode
;     <o.5> CLK_SRC
;       <0=> Externally supplied clock used by Flash
;       <1=> Internal clock source used by Flash
FOPT            EQU     0xFF
;   </h>
;   <h> Flash security byte (FSEC)
;     <i> WARNING: If SEC field is configured as "MCU security status is secure" and MEEN field is configured as "Mass erase is disabled",
;     <i> MCU's security status cannot be set back to unsecure state since Mass erase via the debugger is blocked !!!
;     <o.0..1> SEC
;       <2=> MCU security status is unsecure
;       <3=> MCU security status is secure
;         <i> Flash Security
;     <o.2..3> FSLACC
;       <2=> Freescale factory access denied
;       <3=> Freescale factory access granted
;         <i> Freescale Failure Analysis Access Code
;     <o.4..5> MEEN
;       <2=> Mass erase is disabled
;       <3=> Mass erase is enabled
;     <o.6..7> KEYEN
;       <2=> Backdoor key access enabled
;       <3=> Backdoor key access disabled
;         <i> Backdoor Key Security Enable
FSEC            EQU     0xFE
;   </h>
; </h>
                IF      :LNOT::DEF:RAM_TARGET
                AREA    |.ARM.__at_0x400|, CODE, READONLY
                DCB     BackDoorK0, BackDoorK1, BackDoorK2, BackDoorK3
                DCB     BackDoorK4, BackDoorK5, BackDoorK6, BackDoorK7
                DCB     FPROT0    , FPROT1    , FPROT2    , FPROT3
                DCB     FSEC      , FOPT      , 0xFF      , 0xFF
                ENDIF

_NVIC_ICER      EQU   0xE000E180
_NVIC_ICPR      EQU   0xE000E280

                AREA    |.text|, CODE, READONLY

; Reset Handler

Reset_Handler   PROC
                EXPORT  Reset_Handler             [WEAK]
                IMPORT  SystemInit
                IMPORT  __main

                CPSID   I               ; Mask interrupts
                LDR R0, =_NVIC_ICER    ; Disable interrupts and clear pending flags
                LDR R1, =_NVIC_ICPR
                LDR R2, =0xFFFFFFFF
                STR R2, [R0]        ; NVIC_ICER - clear enable IRQ register
                STR R2, [R1]        ; NVIC_ICPR - clear pending IRQ register
                LDR     R0, =SystemInit
                BLX     R0
                CPSIE   i               ; Unmask interrupts
                LDR     R0, =__main
                BX      R0
                ENDP


; Dummy Exception Handlers (infinite loops which can be modified)
NMI_Handler\
                PROC
                EXPORT  NMI_Handler         [WEAK]
                B       .
                ENDP
HardFault_Handler\
                PROC
                EXPORT  HardFault_Handler         [WEAK]
                B       .
                ENDP
SVC_Handler\
                PROC
                EXPORT  SVC_Handler         [WEAK]
                B       .
                ENDP
PendSV_Handler\
                PROC
                EXPORT  PendSV_Handler         [WEAK]
                B       .
                ENDP
SysTick_Handler\
                PROC
                EXPORT  SysTick_Handler         [WEAK]
                B       .
                ENDP
Default_Handler\
                PROC
                EXPORT  DMA0_IRQHandler         [WEAK]
                EXPORT  DMA1_IRQHandler         [WEAK]
                EXPORT  DMA2_IRQHandler         [WEAK]
                EXPORT  DMA3_IRQHandler         [WEAK]
                EXPORT  SPI0_IRQHandler         [WEAK]
                EXPORT  SPI1_IRQHandler         [WEAK]
                EXPORT  PMC_IRQHandler         [WEAK]
                EXPORT  TMR0_IRQHandler         [WEAK]
                EXPORT  TMR1_IRQHandler         [WEAK]
                EXPORT  TMR2_IRQHandler         [WEAK]
                EXPORT  TMR3_IRQHandler         [WEAK]
                EXPORT  PIT0_PIT1_IRQHandler         [WEAK]
                EXPORT  LLWU_IRQHandler         [WEAK]
                EXPORT  FTFA_IRQHandler         [WEAK]
                EXPORT  CMP0_CMP1_IRQHandler         [WEAK]
                EXPORT  LCD_IRQHandler         [WEAK]
                EXPORT  ADC_IRQHandler         [WEAK]
                EXPORT  PTx_IRQHandler         [WEAK]
                EXPORT  RNGA_IRQHandler         [WEAK]
                EXPORT  UART0_UART1_IRQHandler         [WEAK]
                EXPORT  UART2_UART3_IRQHandler         [WEAK]
                EXPORT  AFE_CH0_IRQHandler         [WEAK]
                EXPORT  AFE_CH1_IRQHandler         [WEAK]
                EXPORT  AFE_CH2_IRQHandler         [WEAK]
                EXPORT  AFE_CH3_IRQHandler         [WEAK]
                EXPORT  RTC_IRQHandler         [WEAK]
                EXPORT  I2C0_I2C1_IRQHandler         [WEAK]
                EXPORT  EWM_IRQHandler         [WEAK]
                EXPORT  MCG_IRQHandler         [WEAK]
                EXPORT  Watchdog_IRQHandler         [WEAK]
                EXPORT  LPTMR_IRQHandler         [WEAK]
                EXPORT  XBAR_IRQHandler         [WEAK]
                EXPORT  DefaultISR         [WEAK]
DMA0_IRQHandler
DMA1_IRQHandler
DMA2_IRQHandler
DMA3_IRQHandler
SPI0_IRQHandler
SPI1_IRQHandler
PMC_IRQHandler
TMR0_IRQHandler
TMR1_IRQHandler
TMR2_IRQHandler
TMR3_IRQHandler
PIT0_PIT1_IRQHandler
LLWU_IRQHandler
FTFA_IRQHandler
CMP0_CMP1_IRQHandler
LCD_IRQHandler
ADC_IRQHandler
PTx_IRQHandler
RNGA_IRQHandler
UART0_UART1_IRQHandler
UART2_UART3_IRQHandler
AFE_CH0_IRQHandler
AFE_CH1_IRQHandler
AFE_CH2_IRQHandler
AFE_CH3_IRQHandler
RTC_IRQHandler
I2C0_I2C1_IRQHandler
EWM_IRQHandler
MCG_IRQHandler
Watchdog_IRQHandler
LPTMR_IRQHandler
XBAR_IRQHandler
DefaultISR
                B       .
                ENDP
                  ALIGN


; User Initial Stack & Heap

                IF      :DEF:__MICROLIB

                EXPORT  __initial_sp
                EXPORT  __heap_base
                EXPORT  __heap_limit

                ELSE

                IMPORT  __use_two_region_memory
                EXPORT  __user_initial_stackheap
__user_initial_stackheap

                LDR     R0, =  Heap_Mem
                LDR     R1, =(Stack_Mem + Stack_Size)
                LDR     R2, = (Heap_Mem +  Heap_Size)
                LDR     R3, = Stack_Mem
                BX      LR

                ALIGN

                ENDIF


                END
